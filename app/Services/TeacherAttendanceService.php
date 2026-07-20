<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\DailyAttendance;
use App\Models\Teacher;
use App\Models\TransportSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class TeacherAttendanceService
{
    public function record(
        Teacher $teacher,
        string $mode,
        ?string $photoBase64 = null
    ): array {
        if (! in_array($mode, ['check_in', 'check_out'], true)) {
            throw new InvalidArgumentException('Mode absensi tidak valid.');
        }

        $now = now();

        $attendance = DailyAttendance::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'attendance_date' => $now->toDateString(),
            ],
            [
                'status' => 'present',
                'transport_amount' => 0,
            ]
        );

        return $mode === 'check_in'
            ? $this->recordCheckIn($attendance, $teacher, $now, $photoBase64)
            : $this->recordCheckOut($attendance, $teacher, $now, $photoBase64);
    }

    private function recordCheckIn(
        DailyAttendance $attendance,
        Teacher $teacher,
        Carbon $now,
        ?string $photoBase64
    ): array {
        if ($attendance->check_in_time) {
            return [
                'status' => 'already',
                'type' => 'check_in',
                'name' => $teacher->name,
                'transport' => (int) $attendance->transport_amount,
                'message' => $teacher->name
                    . ' sudah absen masuk pukul '
                    . substr((string) $attendance->check_in_time, 0, 5)
                    . '.',
            ];
        }

        $setting = $this->activeSetting();

        $isValidCheckIn = $setting
            ? $this->isWithinWindow(
                $now,
                (string) $setting->check_in_start,
                (string) $setting->check_in_end
            )
            : false;

        $photoPath = $this->saveBase64Photo(
            $photoBase64,
            'attendance/check-in'
        );

        $attendance->update([
            'attendance_time' => $now->format('H:i:s'),
            'check_in_time' => $now->format('H:i:s'),
            'check_in_status' => $isValidCheckIn ? 'ontime' : 'late',
            'check_in_photo' => $photoPath,
            'status' => 'present',
            'transport_amount' => 0,
            'note' => $setting
                ? (
                    $isValidCheckIn
                        ? 'Absen masuk sesuai jadwal. Transport menunggu absen pulang.'
                        : 'Absen masuk di luar jadwal transport.'
                )
                : 'Absen masuk tercatat, tetapi pengaturan transport belum aktif.',
        ]);

        return [
            'status' => 'success',
            'type' => 'check_in',
            'name' => $teacher->name,
            'check_in_status' => $isValidCheckIn ? 'ontime' : 'late',
            'transport' => 0,
            'photo' => $photoPath,
            'message' => $teacher->name
                . ' berhasil absen masuk pukul '
                . $now->format('H:i')
                . '. Transport menunggu absen pulang.',
        ];
    }

    private function recordCheckOut(
        DailyAttendance $attendance,
        Teacher $teacher,
        Carbon $now,
        ?string $photoBase64
    ): array {
        if (! $attendance->check_in_time) {
            return [
                'status' => 'no_check_in',
                'type' => 'check_out',
                'name' => $teacher->name,
                'transport' => 0,
                'message' => $teacher->name
                    . ' belum melakukan absen masuk hari ini.',
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'status' => 'already',
                'type' => 'check_out',
                'name' => $teacher->name,
                'transport' => (int) $attendance->transport_amount,
                'message' => $teacher->name
                    . ' sudah absen pulang pukul '
                    . substr((string) $attendance->check_out_time, 0, 5)
                    . '.',
            ];
        }

        $setting = $this->activeSetting();

        $isValidCheckIn = $attendance->check_in_status === 'ontime';

        $isValidCheckOut = $setting
            ? $this->isWithinWindow(
                $now,
                (string) $setting->check_out_start,
                (string) $setting->check_out_end
            )
            : false;

        $transportAmount = (
            $setting
            && $isValidCheckIn
            && $isValidCheckOut
        )
            ? (int) $setting->amount
            : 0;

        $photoPath = $this->saveBase64Photo(
            $photoBase64,
            'attendance/check-out'
        );

        $attendance->update([
            'check_out_time' => $now->format('H:i:s'),
            'check_out_status' => $isValidCheckOut ? 'normal' : 'early',
            'check_out_photo' => $photoPath,
            'transport_amount' => $transportAmount,
            'note' => $setting
                ? (
                    $transportAmount > 0
                        ? 'Absen masuk dan pulang sesuai jadwal. Transport diberikan.'
                        : 'Transport tidak diberikan karena waktu masuk atau pulang tidak sesuai jadwal.'
                )
                : 'Absen pulang tercatat, tetapi pengaturan transport belum aktif.',
        ]);

        if ($transportAmount > 0) {
            AppNotification::create([
                'teacher_id' => $teacher->id,
                'title' => 'Transport diterima',
                'message' => 'Anda mendapatkan transport sebesar Rp '
                    . number_format($transportAmount, 0, ',', '.')
                    . ' hari ini.',
                'type' => 'success',
            ]);
        }

        return [
            'status' => 'success',
            'type' => 'check_out',
            'name' => $teacher->name,
            'check_out_status' => $isValidCheckOut ? 'normal' : 'early',
            'transport' => $transportAmount,
            'photo' => $photoPath,
            'message' => $teacher->name
                . ' berhasil absen pulang pukul '
                . $now->format('H:i')
                . '. Transport: Rp '
                . number_format($transportAmount, 0, ',', '.')
                . '.',
        ];
    }

    private function activeSetting(): ?TransportSetting
    {
        return TransportSetting::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    private function isWithinWindow(
        Carbon $now,
        string $start,
        string $end
    ): bool {
        $startTime = Carbon::parse(
            $now->toDateString() . ' ' . substr($start, 0, 8)
        );

        $endTime = Carbon::parse(
            $now->toDateString() . ' ' . substr($end, 0, 8)
        );

        if ($endTime->lt($startTime)) {
            $endTime->addDay();

            if ($now->lt($startTime)) {
                $now = $now->copy()->addDay();
            }
        }

        return $now->betweenIncluded($startTime, $endTime);
    }

    private function saveBase64Photo(
        ?string $photoBase64,
        string $folder
    ): ?string {
        if (! $photoBase64) {
            return null;
        }

        if (! preg_match(
            '/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/s',
            $photoBase64,
            $matches
        )) {
            return null;
        }

        $extension = match (strtolower($matches[1])) {
            'jpeg', 'jpg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            default => 'jpg',
        };

        $binary = base64_decode(
            str_replace(' ', '+', $matches[2]),
            true
        );

        if ($binary === false) {
            return null;
        }

        $fileName = $folder
            . '/'
            . uniqid('attendance_', true)
            . '.'
            . $extension;

        Storage::disk('public')->put($fileName, $binary);

        return $fileName;
    }
}

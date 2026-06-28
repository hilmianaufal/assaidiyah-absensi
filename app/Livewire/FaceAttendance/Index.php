<?php

namespace App\Livewire\FaceAttendance;

use App\Models\DailyAttendance;
use App\Models\Teacher;
use App\Models\TransportSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Index extends Component
{
    public string $mode = 'check_in';

    public array $logs = [];

    public function setMode(string $mode): void
    {
        if (! in_array($mode, ['check_in', 'check_out'])) {
            return;
        }

        $this->mode = $mode;
    }

    public function saveAttendanceByTeacherId($teacherId, ?string $photoBase64 = null): array
    {
        $teacher = Teacher::findOrFail($teacherId);
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

        if ($this->mode === 'check_in') {
            return $this->saveCheckIn($attendance, $teacher, $now);
        }

        return $this->saveCheckOut($attendance, $teacher, $now);
    }

    private function setting(): ?TransportSetting
    {
        return TransportSetting::where('is_active', true)->first();
    }

    private function isBetween(Carbon $now, string $start, string $end): bool
    {
        $startTime = Carbon::createFromTimeString($start);
        $endTime = Carbon::createFromTimeString($end);

        return $now->betweenIncluded($startTime, $endTime);
    }

    private function saveCheckIn(DailyAttendance $attendance, Teacher $teacher, Carbon $now): array
    {
        if ($attendance->check_in_time) {
            return [
                'status' => 'already',
                'type' => 'check_in',
                'name' => $teacher->name,
                'message' => $teacher->name . ' sudah absen masuk pukul ' . substr($attendance->check_in_time, 0, 5),
            ];
        }

        $setting = $this->setting();

        $isValidCheckIn = false;

        if ($setting) {
            $isValidCheckIn = $this->isBetween(
                $now,
                $setting->check_in_start,
                $setting->check_in_end
            );
        }

        $attendance->update([
            'attendance_time' => $now->format('H:i:s'),
            'check_in_time' => $now->format('H:i:s'),
            'check_in_status' => $isValidCheckIn ? 'ontime' : 'late',
            'transport_amount' => 0,
            'note' => $isValidCheckIn
                ? 'Absen masuk valid. Transport menunggu absen pulang.'
                : 'Absen masuk di luar waktu transport.',
        ]);

        $this->addLog($teacher->name, 'Masuk', $now->format('H:i:s'), 0);

        return [
            'status' => 'success',
            'type' => 'check_in',
            'name' => $teacher->name,
            'transport' => 0,
            'message' => $teacher->name . ' berhasil absen masuk pukul ' . $now->format('H:i') . '. Transport menunggu absen pulang.',
        ];
    }

    private function saveCheckOut(DailyAttendance $attendance, Teacher $teacher, Carbon $now): array
    {
        if (! $attendance->check_in_time) {
            return [
                'status' => 'no_check_in',
                'type' => 'check_out',
                'name' => $teacher->name,
                'message' => $teacher->name . ' belum absen masuk hari ini.',
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'status' => 'already',
                'type' => 'check_out',
                'name' => $teacher->name,
                'message' => $teacher->name . ' sudah absen pulang pukul ' . substr($attendance->check_out_time, 0, 5),
            ];
        }

        $setting = $this->setting();

        $isValidCheckIn = $attendance->check_in_status === 'ontime';
        $isValidCheckOut = false;
        $transportAmount = 0;

        if ($setting) {
            $isValidCheckOut = $this->isBetween(
                $now,
                $setting->check_out_start,
                $setting->check_out_end
            );

            if ($isValidCheckIn && $isValidCheckOut) {
                $transportAmount = (int) $setting->amount;
            }
        }

        $attendance->update([
            'check_out_time' => $now->format('H:i:s'),
            'check_out_status' => $isValidCheckOut ? 'normal' : 'early',
            'transport_amount' => $transportAmount,
            'note' => $transportAmount > 0
                ? 'Absen masuk dan pulang valid. Mendapat transport.'
                : 'Transport tidak cair karena absen masuk/pulang tidak sesuai waktu.',
        ]);

        $this->addLog($teacher->name, 'Pulang', $now->format('H:i:s'), $transportAmount);
        if ($transportAmount > 0) {
                \App\Models\AppNotification::create([
                    'teacher_id' => $teacher->id,
                    'title' => 'Transport diterima',
                    'message' => 'Anda mendapatkan transport sebesar Rp ' . number_format($transportAmount, 0, ',', '.') . ' hari ini.',
                    'type' => 'success',
                ]);
            }
        return [
            'status' => 'success',
            'type' => 'check_out',
            'name' => $teacher->name,
            'checkout_status' => $isValidCheckOut ? 'normal' : 'early',
            'transport' => $transportAmount,
            'message' => $teacher->name . ' berhasil absen pulang pukul ' . $now->format('H:i') .
                '. Transport: Rp' . number_format($transportAmount, 0, ',', '.'),
        ];
    }

    private function addLog(string $name, string $type, string $time, int $transport): void
    {
        $this->logs = array_slice(array_merge([[
            'name' => $name,
            'type' => $type,
            'time' => $time,
            'transport' => $transport,
        ]], $this->logs), 0, 10);
    }

    public function render()
    {

     return view('livewire.face-attendance.index', [
            'teachers' => Teacher::where('is_active', true)
                ->whereNotNull('face_descriptor')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }

    private function saveBase64Photo(?string $photoBase64, string $folder): ?string
    {
        if (! $photoBase64) {
            return null;
        }

        $image = str_replace('data:image/jpeg;base64,', '', $photoBase64);
        $image = str_replace(' ', '+', $image);

        $fileName = $folder . '/' . uniqid() . '.jpg';

        Storage::disk('public')->put($fileName, base64_decode($image));

        return $fileName;
    }
}

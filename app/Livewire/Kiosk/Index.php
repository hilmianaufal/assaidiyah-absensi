<?php

namespace App\Livewire\Kiosk;

use App\Models\DailyAttendance;
use App\Models\Teacher;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Index extends Component
{
    public string $mode = 'check_in';

    public function setMode(string $mode): void
    {
        if (in_array($mode, ['check_in', 'check_out'])) {
            $this->mode = $mode;
        }
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
            return $this->saveCheckIn($attendance, $teacher, $now, $photoBase64);
        }

        return $this->saveCheckOut($attendance, $teacher, $now, $photoBase64);
    }

    private function saveCheckIn(
        DailyAttendance $attendance,
        Teacher $teacher,
        Carbon $now,
        ?string $photoBase64 = null
    ): array {
        if ($attendance->check_in_time) {
            return [
                'status' => 'already',
                'type' => 'check_in',
                'name' => $teacher->name,
                'message' => 'Sudah absen masuk pukul ' . substr($attendance->check_in_time, 0, 5),
            ];
        }

        $start = Carbon::createFromTimeString('06:45:00');
        $end = Carbon::createFromTimeString('07:15:00');

        $isTransport = $now->betweenIncluded($start, $end);

        $photoPath = $this->saveBase64Photo($photoBase64, 'attendance/check-in');

        $attendance->update([
            'attendance_time' => $now->format('H:i:s'),
            'check_in_time' => $now->format('H:i:s'),
            'check_in_status' => $isTransport ? 'ontime' : 'late',
            'check_in_photo' => $photoPath,
            'transport_amount' => $isTransport ? 10000 : 0,
            'note' => $isTransport
                ? 'Hadir tepat waktu, mendapat transport.'
                : 'Hadir di luar waktu transport, hanya menggugurkan kewajiban.',
        ]);

        $message = "
                Assalamu'alaikum Wr. Wb.

                Absensi berhasil dicatat.

                Nama :
                {$teacher->name}

                Tanggal :
                ".now()->format('d-m-Y')."

                Jam Masuk :
                ".$now->format('H:i')."

                Status :
                ".($isTransport ? 'Tepat Waktu' : 'Terlambat')."

                Transport :
                Rp ".number_format($isTransport ? 10000 : 0,0,',','.')."

                Sistem Absensi Assaidiyyah
                ";

                if ($teacher->phone) {
                    WhatsappService::send(
                        $teacher->phone,
                        $message
                    );
                }

        return [
            'status' => 'success',
            'type' => 'check_in',
            'name' => $teacher->name,
            'transport' => $isTransport ? 10000 : 0,
            'photo' => $photoPath,
            'message' => $isTransport
                ? 'Transport Rp10.000'
                : 'Tidak mendapat transport',
        ];
    }

    private function saveCheckOut(
        DailyAttendance $attendance,
        Teacher $teacher,
        Carbon $now,
        ?string $photoBase64 = null
    ): array {
        if (! $attendance->check_in_time) {
            return [
                'status' => 'no_check_in',
                'type' => 'check_out',
                'name' => $teacher->name,
                'message' => 'Belum absen masuk hari ini.',
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'status' => 'already',
                'type' => 'check_out',
                'name' => $teacher->name,
                'message' => 'Sudah absen pulang pukul ' . substr($attendance->check_out_time, 0, 5),
            ];
        }

        $minimumCheckout = Carbon::createFromTimeString('13:00:00');
        $isNormal = $now->greaterThanOrEqualTo($minimumCheckout);

        $photoPath = $this->saveBase64Photo($photoBase64, 'attendance/check-out');

        $attendance->update([
            'check_out_time' => $now->format('H:i:s'),
            'check_out_status' => $isNormal ? 'normal' : 'early',
            'check_out_photo' => $photoPath,
        ]);

        return [
            'status' => 'success',
            'type' => 'check_out',
            'name' => $teacher->name,
            'checkout_status' => $isNormal ? 'normal' : 'early',
            'photo' => $photoPath,
            'message' => 'Absensi pulang berhasil.',
        ];
    }

    private function saveBase64Photo(?string $photoBase64, string $folder): ?string
    {
        if (! $photoBase64) {
            return null;
        }

        if (! str_contains($photoBase64, 'base64,')) {
            return null;
        }

        [$meta, $image] = explode('base64,', $photoBase64);

        $image = str_replace(' ', '+', $image);

        $fileName = $folder . '/' . uniqid('attendance_', true) . '.jpg';

        Storage::disk('public')->put($fileName, base64_decode($image));

        return $fileName;
    }

    public function render()
    {
        return view('livewire.kiosk.index', [
            'teachers' => Teacher::where('is_active', true)
                ->whereNotNull('face_descriptor')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
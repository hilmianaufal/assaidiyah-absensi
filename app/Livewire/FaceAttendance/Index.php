<?php

namespace App\Livewire\FaceAttendance;

use App\Models\DailyAttendance;
use App\Models\Teacher;
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

        $start = Carbon::createFromTimeString('06:45:00');
        $end = Carbon::createFromTimeString('07:15:00');

        $isTransport = $now->betweenIncluded($start, $end);

        $attendance->update([
            'attendance_time' => $now->format('H:i:s'),
            'check_in_time' => $now->format('H:i:s'),
            'check_in_status' => $isTransport ? 'ontime' : 'late',
            'transport_amount' => $isTransport ? 10000 : 0,
            'note' => $isTransport
                ? 'Hadir tepat waktu, mendapat transport.'
                : 'Hadir di luar waktu transport, hanya menggugurkan kewajiban.',
        ]);

        $this->addLog($teacher->name, 'Masuk', $now->format('H:i:s'), $isTransport ? 10000 : 0);

        return [
            'status' => 'success',
            'type' => 'check_in',
            'name' => $teacher->name,
            'transport' => $isTransport ? 10000 : 0,
            'message' => $teacher->name . ' berhasil absen masuk pukul ' . $now->format('H:i'),
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

        $minimumCheckout = Carbon::createFromTimeString('13:00:00');
        $isNormal = $now->greaterThanOrEqualTo($minimumCheckout);

        $attendance->update([
            'check_out_time' => $now->format('H:i:s'),
            'check_out_status' => $isNormal ? 'normal' : 'early',
        ]);

        $this->addLog($teacher->name, 'Pulang', $now->format('H:i:s'), $attendance->transport_amount);

        return [
            'status' => 'success',
            'type' => 'check_out',
            'name' => $teacher->name,
            'checkout_status' => $isNormal ? 'normal' : 'early',
            'message' => $teacher->name . ' berhasil absen pulang pukul ' . $now->format('H:i'),
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
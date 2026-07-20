<?php

namespace App\Livewire\FaceAttendance;

use App\Models\Teacher;
use App\Services\TeacherAttendanceService;
use Livewire\Component;

class Index extends Component
{
    public string $mode = 'check_in';

    public array $logs = [];

    public function mount(): void
    {
        $requestedMode = (string) request()->query(
            'mode',
            'check_in'
        );

        if (in_array(
            $requestedMode,
            ['check_in', 'check_out'],
            true
        )) {
            $this->mode = $requestedMode;
        }
    }

    public function setMode(string $mode): void
    {
        if (! in_array(
            $mode,
            ['check_in', 'check_out'],
            true
        )) {
            return;
        }

        $this->mode = $mode;
    }

    public function saveAttendanceByTeacherId(
        $teacherId,
        ?string $photoBase64 = null
    ): array {
        $teacher = Teacher::query()
            ->where('is_active', true)
            ->findOrFail($teacherId);

        $result = app(TeacherAttendanceService::class)
            ->record(
                $teacher,
                $this->mode,
                $photoBase64
            );

        if (($result['status'] ?? null) === 'success') {
            $this->addLog(
                (string) $result['name'],
                $this->mode === 'check_in'
                    ? 'Masuk'
                    : 'Pulang',
                now()->format('H:i:s'),
                (int) ($result['transport'] ?? 0)
            );
        }

        return $result;
    }

    private function addLog(
        string $name,
        string $type,
        string $time,
        int $transport
    ): void {
        $newLog = [
            'name' => $name,
            'type' => $type,
            'time' => $time,
            'transport' => $transport,
        ];

        $this->logs = array_slice(
            array_merge([$newLog], $this->logs),
            0,
            10
        );
    }

    public function render()
    {
        return view('livewire.face-attendance.index', [
            'teachers' => Teacher::query()
                ->where('is_active', true)
                ->whereNotNull('face_descriptor')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}

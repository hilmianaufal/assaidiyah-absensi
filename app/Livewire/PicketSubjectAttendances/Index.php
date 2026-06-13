<?php

namespace App\Livewire\PicketSubjectAttendances;

use App\Models\SubjectAttendance;
use App\Models\TeacherPicketSchedule;
use App\Models\TeachingSchedule;
use Livewire\Component;

class Index extends Component
{
    public string $dayName = '';
    public bool $isAllowed = false;

    public function mount(): void
    {
        $teacher = auth()->user()->teacher;

        $this->dayName = $this->currentDayName();

        if (! $teacher || ! $teacher->is_picket_officer) {
            $this->isAllowed = false;
            return;
        }

        $this->isAllowed = TeacherPicketSchedule::where('teacher_id', $teacher->id)
            ->where('day', $this->dayName)
            ->where('is_active', true)
            ->exists();
    }

    private function currentDayName(): string
    {
        return [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Ahad',
        ][now()->format('l')];
    }

    public function markAttendance(int $scheduleId, string $status): void
    {
        if (! $this->isAllowed) {
            abort(403);
        }

        if (! in_array($status, ['present', 'late', 'permit', 'sick', 'absent'])) {
            return;
        }

        $picketTeacher = auth()->user()->teacher;

        $schedule = TeachingSchedule::with(['teacher', 'subject'])
            ->findOrFail($scheduleId);

        $isPaid = in_array($status, ['present', 'late']);

        $teachingHonor = $isPaid
            ? $schedule->hours_count * $schedule->teacher->hourly_rate
            : 0;

        SubjectAttendance::updateOrCreate(
            [
                'teacher_id' => $schedule->teacher_id,
                'subject_id' => $schedule->subject_id,
                'teaching_schedule_id' => $schedule->id,
                'teaching_date' => now()->toDateString(),
            ],
            [
                'recorded_by_teacher_id' => $picketTeacher->id,
                'source' => 'picket',
                'attendance_status' => $status,
                'recorded_at' => now(),

                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'hours_count' => $schedule->hours_count,
                'hourly_rate' => $schedule->teacher->hourly_rate,
                'teaching_honor' => $teachingHonor,
                'class_name' => $schedule->class_name,
                'status' => $status,
                'note' => 'Dicatat oleh guru piket: ' . $picketTeacher->name,
            ]
        );

        session()->flash('success', 'Absensi mapel berhasil dicatat.');
    }

    public function render()
    {
        $schedules = TeachingSchedule::with(['teacher', 'subject'])
            ->where('day', $this->dayName)
            ->orderBy('start_time')
            ->get();

        $attendances = SubjectAttendance::whereDate('teaching_date', now()->toDateString())
            ->get()
            ->keyBy('teaching_schedule_id');

        return view('livewire.picket-subject-attendances.index', [
            'schedules' => $schedules,
            'attendances' => $attendances,
        ])->layout('layouts.app');
    }
}

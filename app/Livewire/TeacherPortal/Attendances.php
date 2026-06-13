<?php

namespace App\Livewire\TeacherPortal;

use App\Models\DailyAttendance;
use Livewire\Component;
use Livewire\WithPagination;

class Attendances extends Component
{
    use WithPagination;

    public string $month;
    public string $year;

    public function mount(): void
    {
        $this->month = now()->format('m');
        $this->year = now()->format('Y');
    }

    public function render()
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            abort(403, 'Akun ini belum terhubung dengan data guru.');
        }

        $attendances = DailyAttendance::where('teacher_id', $teacher->id)
            ->whereMonth('attendance_date', $this->month)
            ->whereYear('attendance_date', $this->year)
            ->latest('attendance_date')
            ->paginate(10);

        return view('livewire.teacher-portal.attendances', [
            'teacher' => $teacher,
            'attendances' => $attendances,
        ])->layout('layouts.app');
    }
}
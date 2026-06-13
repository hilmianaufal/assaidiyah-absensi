<?php

namespace App\Livewire\TeacherPortal;

use App\Models\AdditionalHonor;
use App\Models\MonthlyHonor;
use App\Models\SubjectAttendance;
use Livewire\Component;

class Honors extends Component
{
    public int $month;
    public int $year;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function render()
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            abort(403, 'Akun ini belum terhubung dengan data guru.');
        }

        $honor = MonthlyHonor::where('teacher_id', $teacher->id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();

        $subjectAttendances = SubjectAttendance::with('subject')
            ->where('teacher_id', $teacher->id)
            ->whereMonth('teaching_date', $this->month)
            ->whereYear('teaching_date', $this->year)
            ->latest('teaching_date')
            ->get();
$additionalHonors = AdditionalHonor::where('teacher_id', $teacher->id)
    ->where('month', $this->month)
    ->where('year', $this->year)
    ->latest()
    ->get();
        return view('livewire.teacher-portal.honors', [
            'teacher' => $teacher,
            'honor' => $honor,
            'subjectAttendances' => $subjectAttendances,
            'additionalHonors' => $additionalHonors,
        ])->layout('layouts.app');
    }
}

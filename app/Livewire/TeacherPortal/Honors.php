<?php

namespace App\Livewire\TeacherPortal;

use App\Models\AdditionalHonor;
use App\Models\DailyAttendance;
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

        $honors = MonthlyHonor::with(['institution', 'payments'])
            ->where('teacher_id', $teacher->id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get();

        $honor = $honors->first();

        $subjectAttendances = SubjectAttendance::with(['subject', 'institution'])
            ->where('teacher_id', $teacher->id)
            ->whereMonth('teaching_date', $this->month)
            ->whereYear('teaching_date', $this->year)
            ->latest('teaching_date')
            ->get();

        $runningTeachingHonor = $subjectAttendances
            ->whereIn('attendance_status', ['present', 'late'])
            ->sum('teaching_honor');

        $runningHours = $subjectAttendances
            ->whereIn('attendance_status', ['present', 'late'])
            ->sum('hours_count');

        $additionalHonors = AdditionalHonor::with('institution')
            ->where('teacher_id', $teacher->id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->latest()
            ->get();
        $runningTransport = DailyAttendance::where('teacher_id', $teacher->id)
                        ->whereMonth('attendance_date', $this->month)
                        ->whereYear('attendance_date', $this->year)
                        ->sum('transport_amount');
        return view('livewire.teacher-portal.honors', [
            'teacher' => $teacher,
            'honor' => $honor,
            'honors' => $honors,
            'subjectAttendances' => $subjectAttendances,
            'additionalHonors' => $additionalHonors,
            'runningTeachingHonor' => $runningTeachingHonor,
            'runningHours' => $runningHours,
            'totalHonor' => $honors->sum('grand_total'),
            'runningTransport' => $runningTransport,
        ])->layout('layouts.app');
    }
}

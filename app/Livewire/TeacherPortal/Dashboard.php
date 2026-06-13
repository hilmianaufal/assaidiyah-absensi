<?php

namespace App\Livewire\TeacherPortal;

use App\Models\DailyAttendance;
use App\Models\MonthlyHonor;
use App\Models\SubjectAttendance;
use App\Models\TeachingSchedule;
use App\Models\TeacherPicketSchedule;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Akun ini belum terhubung dengan data guru.');
        }

        $today = now()->toDateString();
        $month = now()->month;
        $year = now()->year;

        $todayAttendance = DailyAttendance::where('teacher_id', $teacher->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $monthlyHonor = MonthlyHonor::where('teacher_id', $teacher->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $teachingToday = SubjectAttendance::with('subject')
            ->where('teacher_id', $teacher->id)
            ->whereDate('teaching_date', $today)
            ->latest()
            ->get();

        $schedules = TeachingSchedule::with('subject')
            ->where('teacher_id', $teacher->id)
            ->orderBy('day')
            ->get();

        $todayName = now()->locale('id')->translatedFormat('l');

        $picketScheduleToday = TeacherPicketSchedule::where('teacher_id', $teacher->id)
            ->where('day', $todayName)
            ->where('is_active', true)
            ->first();

        return view('livewire.teacher-portal.dashboard', [
            'teacher' => $teacher,
            'todayAttendance' => $todayAttendance,
            'monthlyHonor' => $monthlyHonor,
            'teachingToday' => $teachingToday,
            'schedules' => $schedules,
            'picketScheduleToday' => $picketScheduleToday,
        ])->layout('layouts.app');
    }
}
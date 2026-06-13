<?php

namespace App\Livewire\Dashboard;

use App\Models\DailyAttendance;
use App\Models\MonthlyHonor;
use App\Models\Teacher;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $today = now()->toDateString();
        $month = now()->month;
        $year = now()->year;

        $totalTeachers = Teacher::where('is_active', true)->count();

        $presentToday = DailyAttendance::whereDate('attendance_date', $today)
            ->whereNotNull('check_in_time')
            ->count();

        $notPresentToday = max($totalTeachers - $presentToday, 0);

        $lateToday = DailyAttendance::whereDate('attendance_date', $today)
            ->where('check_in_status', 'late')
            ->count();

        $checkedOutToday = DailyAttendance::whereDate('attendance_date', $today)
            ->whereNotNull('check_out_time')
            ->count();

        $transportToday = DailyAttendance::whereDate('attendance_date', $today)
            ->sum('transport_amount');

        $honorThisMonth = MonthlyHonor::where('month', $month)
            ->where('year', $year)
            ->sum('grand_total');

        $recentActivities = DailyAttendance::with('teacher')
            ->whereDate('attendance_date', $today)
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('livewire.dashboard.index', [
            'totalTeachers' => $totalTeachers,
            'presentToday' => $presentToday,
            'notPresentToday' => $notPresentToday,
            'lateToday' => $lateToday,
            'checkedOutToday' => $checkedOutToday,
            'transportToday' => $transportToday,
            'honorThisMonth' => $honorThisMonth,
            'recentActivities' => $recentActivities,
        ])->layout('layouts.app');
    }
}
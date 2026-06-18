<?php

namespace App\Livewire\FinanceDashboard;

use App\Models\HonorPayment;
use App\Models\Institution;
use App\Models\MonthlyHonor;
use App\Models\SubjectAttendance;
use Livewire\Component;

class Index extends Component
{
    public int $month;
    public int $year;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function updatedMonth(): void
    {
        //
    }

    public function updatedYear(): void
    {
        //
    }

    public function render()
    {
        $honors = MonthlyHonor::with(['teacher', 'institution', 'payments'])
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get();

        $totalHonor = (int) $honors->sum('grand_total');

        $totalPaid = (int) HonorPayment::whereHas('monthlyHonor', function ($query) {
            $query->where('month', $this->month)
                ->where('year', $this->year);
        })->sum('amount');

        $totalRemaining = max($totalHonor - $totalPaid, 0);

        $institutionSummaries = Institution::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($institution) use ($honors) {
                $institutionHonors = $honors->where('institution_id', $institution->id);

                $paid = HonorPayment::whereHas('monthlyHonor', function ($query) use ($institution) {
                    $query->where('month', $this->month)
                        ->where('year', $this->year)
                        ->where('institution_id', $institution->id);
                })->sum('amount');

                $total = (int) $institutionHonors->sum('grand_total');



                return [
                    'institution' => $institution,
                    'total_honor' => $total,
                    'total_paid' => (int) $paid,
                    'total_remaining' => max($total - (int) $paid, 0),
                    'total_teachers' => $institutionHonors->pluck('teacher_id')->unique()->count(),
                    'total_records' => $institutionHonors->count(),
                ];
            });
            $runningTeachingHonor = SubjectAttendance::whereMonth('teaching_date', $this->month)
            ->whereYear('teaching_date', $this->year)
            ->whereIn('attendance_status', ['present', 'late'])
            ->sum('teaching_honor');

            $runningHours = SubjectAttendance::whereMonth('teaching_date', $this->month)
                ->whereYear('teaching_date', $this->year)
                ->whereIn('attendance_status', ['present', 'late'])
                ->sum('hours_count');

            $runningByInstitution = Institution::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($institution) {
                    return [
                        'institution' => $institution,
                        'running_honor' => SubjectAttendance::where('institution_id', $institution->id)
                            ->whereMonth('teaching_date', $this->month)
                            ->whereYear('teaching_date', $this->year)
                            ->whereIn('attendance_status', ['present', 'late'])
                            ->sum('teaching_honor'),
                        'running_hours' => SubjectAttendance::where('institution_id', $institution->id)
                            ->whereMonth('teaching_date', $this->month)
                            ->whereYear('teaching_date', $this->year)
                            ->whereIn('attendance_status', ['present', 'late'])
                            ->sum('hours_count'),
                    ];
                });
        return view('livewire.finance-dashboard.index', [
            'totalHonor' => $totalHonor,
            'totalPaid' => $totalPaid,
            'totalRemaining' => $totalRemaining,
            'runningTeachingHonor' => $runningTeachingHonor,
            'runningHours' => $runningHours,
            'runningByInstitution' => $runningByInstitution,
            'totalTeachers' => $honors->pluck('teacher_id')->unique()->count(),
            'totalRecords' => $honors->count(),
            'institutionSummaries' => $institutionSummaries,
            'unpaidHonors' => $honors
                ->filter(fn ($honor) => $honor->payment_status !== 'paid')
                ->take(8),
        ])->layout('layouts.app');
    }
}

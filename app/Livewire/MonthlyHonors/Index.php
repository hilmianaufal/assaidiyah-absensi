<?php

namespace App\Livewire\MonthlyHonors;

use App\Exports\MonthlyHonorsExport;
use App\Models\DailyAttendance;
use App\Models\MonthlyHonor;
use App\Models\SubjectAttendance;
use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public int $month;
    public int $year;

    public function mount(): void
    {
        $this->month = (int) now()->format('m');
        $this->year = (int) now()->format('Y');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function generate(): void
    {
        $teachers = Teacher::where('is_active', true)->get();

        foreach ($teachers as $teacher) {
            $subjectQuery = SubjectAttendance::where('teacher_id', $teacher->id)
                ->whereMonth('teaching_date', $this->month)
                ->whereYear('teaching_date', $this->year);

            $transportQuery = DailyAttendance::where('teacher_id', $teacher->id)
                ->whereMonth('attendance_date', $this->month)
                ->whereYear('attendance_date', $this->year);

            $totalTeachingHours = (int) $subjectQuery->sum('hours_count');
            $totalTeachingHonor = (int) $subjectQuery->sum('teaching_honor');
            $totalTransport = (int) $transportQuery->sum('transport_amount');
            $grandTotal = $totalTeachingHonor + $totalTransport;

            MonthlyHonor::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'month' => $this->month,
                    'year' => $this->year,
                ],
                [
                    'total_teaching_hours' => $totalTeachingHours,
                    'total_teaching_honor' => $totalTeachingHonor,
                    'total_transport' => $totalTransport,
                    'grand_total' => $grandTotal,
                    'payment_status' => 'unpaid',
                ]
            );
        }
    }

    public function markAsPaid(int $id): void
    {
        $honor = MonthlyHonor::findOrFail($id);

        $honor->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function markAsUnpaid(int $id): void
    {
        $honor = MonthlyHonor::findOrFail($id);

        $honor->update([
            'payment_status' => 'unpaid',
            'paid_at' => null,
        ]);
    }

    public function render()
    {
        $honors = MonthlyHonor::query()
            ->with('teacher')
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->when($this->search, fn ($query) =>
                $query->whereHas('teacher', fn ($q) =>
                    $q->where('name', 'like', '%' . $this->search . '%')
                )
            )
            ->latest()
            ->paginate(10);

        return view('livewire.monthly-honors.index', [
            'honors' => $honors,
            'totalGrand' => MonthlyHonor::where('month', $this->month)
                ->where('year', $this->year)
                ->sum('grand_total'),
            'totalTransport' => MonthlyHonor::where('month', $this->month)
                ->where('year', $this->year)
                ->sum('total_transport'),
            'totalTeachingHonor' => MonthlyHonor::where('month', $this->month)
                ->where('year', $this->year)
                ->sum('total_teaching_honor'),
        ])->layout('layouts.app');
    }

    public function exportExcel()
{
    return Excel::download(
        new MonthlyHonorsExport($this->month, $this->year),
        'rekap-honor-' . $this->month . '-' . $this->year . '.xlsx'
    );
}
}
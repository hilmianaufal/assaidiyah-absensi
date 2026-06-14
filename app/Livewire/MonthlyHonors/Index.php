<?php

namespace App\Livewire\MonthlyHonors;

use App\Exports\MonthlyHonorsExport;
use App\Models\MonthlyHonor;
use Illuminate\Support\Facades\Artisan;
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
        Artisan::call('honor:generate-monthly', [
            'month' => $this->month,
            'year' => $this->year,
        ]);

        session()->flash('success', 'Rekap honor berhasil digenerate.');
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

             'totalDeduction' => MonthlyHonor::where('month', $this->month)
            ->where('year', $this->year)
            ->sum('total_deduction'),

            'totalAdditionalHonor' => MonthlyHonor::where('month', $this->month)
                ->where('year', $this->year)
                ->sum('total_additional_honor'),
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

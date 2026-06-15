<?php

namespace App\Livewire\MonthlyHonors;

use App\Exports\MonthlyHonorsExport;
use App\Models\HonorPayment;
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

    public bool $showPaymentModal = false;
    public ?int $selectedHonorId = null;

    public string $payment_date = '';
    public int $payment_amount = 0;
    public string $payment_method = 'cash';
    public string $reference_number = '';
    public string $payment_note = '';

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

    public function openPaymentModal(int $honorId): void
    {
        $honor = MonthlyHonor::with('payments')->findOrFail($honorId);

        $alreadyPaid = (int) $honor->payments()->sum('amount');
        $remaining = max((int) $honor->grand_total - $alreadyPaid, 0);

        $this->selectedHonorId = $honor->id;
        $this->payment_date = now()->toDateString();
        $this->payment_amount = $remaining > 0 ? $remaining : (int) $honor->grand_total;
        $this->payment_method = 'cash';
        $this->reference_number = '';
        $this->payment_note = '';

        $this->showPaymentModal = true;
    }

    public function savePayment(): void
    {
        $this->validate([
            'selectedHonorId' => ['required', 'exists:monthly_honors,id'],
            'payment_date' => ['required', 'date'],
            'payment_amount' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'payment_note' => ['nullable', 'string'],
        ]);

        HonorPayment::create([
            'monthly_honor_id' => $this->selectedHonorId,
            'payment_date' => $this->payment_date,
            'amount' => $this->payment_amount,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number,
            'note' => $this->payment_note,
        ]);

        $honor = MonthlyHonor::findOrFail($this->selectedHonorId);

        $totalPaid = (int) $honor->payments()->sum('amount');

        $honor->update([
            'payment_status' => $totalPaid >= $honor->grand_total ? 'paid' : 'partial',
            'paid_at' => $totalPaid >= $honor->grand_total ? now() : null,
        ]);

        $this->showPaymentModal = false;
        $this->resetPaymentForm();

        session()->flash('success', 'Pembayaran honor berhasil disimpan.');
    }

    public function markAsUnpaid(int $id): void
    {
        $honor = MonthlyHonor::findOrFail($id);

        $honor->payments()->delete();

        $honor->update([
            'payment_status' => 'unpaid',
            'paid_at' => null,
        ]);

        session()->flash('success', 'Status pembayaran dikembalikan menjadi belum dibayar.');
    }

    private function resetPaymentForm(): void
    {
        $this->selectedHonorId = null;
        $this->payment_date = '';
        $this->payment_amount = 0;
        $this->payment_method = 'cash';
        $this->reference_number = '';
        $this->payment_note = '';
        $this->resetValidation();
    }

    public function render()
    {
        $honors = MonthlyHonor::query()
            ->with(['teacher', 'payments'])
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->when($this->search, fn ($query) =>
                $query->whereHas('teacher', fn ($q) =>
                    $q->where('name', 'like', '%' . $this->search . '%')
                )
            )
            ->latest()
            ->paginate(10);
        $totalPaid = \App\Models\HonorPayment::whereHas('monthlyHonor', function ($query) {
            $query->where('month', $this->month)
                ->where('year', $this->year);
        })->sum('amount');

        $totalGrand = MonthlyHonor::where('month', $this->month)
            ->where('year', $this->year)
            ->sum('grand_total');

        return view('livewire.monthly-honors.index', [
            'honors' => $honors,
            'totalPaid' => $totalPaid,
            'totalRemaining' => max($totalGrand - $totalPaid, 0),
            'totalTeachers' => MonthlyHonor::where('month', $this->month)
                ->where('year', $this->year)
                ->count(),
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

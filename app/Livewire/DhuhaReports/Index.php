<?php

namespace App\Livewire\DhuhaReports;

use App\Models\DhuhaReport;
use App\Models\Institution;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $date = '';
    public string $institution_id = '';

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function render()
    {
        return view('livewire.dhuha-reports.index', [
            'reports' => DhuhaReport::with(['teacher', 'institution'])
                ->whereDate('report_date', $this->date)
                ->when($this->institution_id, fn ($q) =>
                    $q->where('institution_id', $this->institution_id)
                )
                ->latest()
                ->paginate(10),

            'institutions' => Institution::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }/*  */
}

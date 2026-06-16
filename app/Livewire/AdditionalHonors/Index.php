<?php

namespace App\Livewire\AdditionalHonors;

use App\Models\AdditionalHonor;
use App\Models\Institution;
use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public int $month;
    public int $year;

    public string $institution_id = '';
    public string $teacher_id = '';
    public string $title = '';
    public int $amount = 0;
    public string $note = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
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

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $honor = AdditionalHonor::findOrFail($id);

        $this->editingId = $honor->id;
        $this->institution_id = (string) $honor->institution_id;
        $this->teacher_id = (string) $honor->teacher_id;
        $this->title = $honor->title;
        $this->month = (int) $honor->month;
        $this->year = (int) $honor->year;
        $this->amount = (int) $honor->amount;
        $this->note = $honor->note ?? '';

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'institution_id' => ['required', 'exists:institutions,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'title' => ['required', 'string', 'max:255'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020'],
            'amount' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string'],
        ]);

        AdditionalHonor::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->showModal = false;
        $this->resetForm();

        session()->flash('success', 'Tambahan honor berhasil disimpan.');
    }

    public function delete(int $id): void
    {
        AdditionalHonor::findOrFail($id)->delete();

        session()->flash('success', 'Tambahan honor berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->institution_id = '';
        $this->teacher_id = '';
        $this->title = '';
        $this->amount = 0;
        $this->note = '';
        $this->resetValidation();
    }

    public function render()
    {
        $additionalHonors = AdditionalHonor::with(['teacher', 'institution'])
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhereHas('teacher', function ($teacherQuery) {
                            $teacherQuery->where('name', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('institution', function ($institutionQuery) {
                            $institutionQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.additional-honors.index', [
            'additionalHonors' => $additionalHonors,
            'institutions' => Institution::where('is_active', true)->orderBy('name')->get(),
            'teachers' => Teacher::where('is_active', true)
                ->when($this->institution_id, function ($query) {
                    $query->whereHas('institutions', function ($q) {
                        $q->where('institutions.id', $this->institution_id);
                    });
                })
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }

    public function updatedInstitutionId(): void
    {
        $this->teacher_id = '';
    }

    public function updated($property): void
    {
        if ($property === 'institution_id') {
            $this->teacher_id = '';
        }
    }
}

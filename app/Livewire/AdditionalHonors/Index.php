<?php

namespace App\Livewire\AdditionalHonors;

use App\Models\AdditionalHonor;
use App\Models\Institution;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public int $month;
    public int $year;

    public ?int $institution_id = null;
    public ?int $teacher_id = null;

    public string $title = '';
    public int $amount = 0;
    public string $note = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    protected string $paginationTheme = 'tailwind';

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

    public function updatedInstitutionId(): void
    {
        $this->teacher_id = null;

        if (! $this->institution_id) {
            return;
        }

        $firstTeacher = Teacher::query()
            ->where('is_active', true)
            ->whereHas('institutions', function ($q) {
                $q->where('institutions.id', $this->institution_id);
            })
            ->orderBy('name')
            ->first();

        $this->teacher_id = $firstTeacher?->id;
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
        $this->institution_id = (int) $honor->institution_id;
        $this->teacher_id = (int) $honor->teacher_id;
        $this->title = $honor->title;
        $this->month = (int) $honor->month;
        $this->year = (int) $honor->year;
        $this->amount = (int) $honor->amount;
        $this->note = $honor->note ?? '';

        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        if (! $this->teacher_id && $this->institution_id) {
            $this->updatedInstitutionId();
        }

        $data = $this->validate([
            'institution_id' => ['required', 'integer', 'exists:institutions,id'],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'title' => ['required', 'string', 'max:255'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'amount' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        AdditionalHonor::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();

        session()->flash('success', 'Tambahan honor berhasil disimpan.');
    }

    public function delete(int $id): void
    {
        AdditionalHonor::findOrFail($id)->delete();

        $this->resetPage();

        session()->flash('success', 'Tambahan honor berhasil dihapus.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->institution_id = null;
        $this->teacher_id = null;
        $this->title = '';
        $this->amount = 0;
        $this->note = '';

        $this->resetValidation();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $additionalHonors = AdditionalHonor::query()
            ->with(['teacher', 'institution'])
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->when($search !== '', function ($query) use ($search) {
                $keyword = '%' . $search . '%';

                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', $keyword)
                        ->orWhere('note', 'like', $keyword)
                        ->orWhereHas('teacher', function ($teacherQuery) use ($keyword) {
                            $teacherQuery->where('name', 'like', $keyword);
                        })
                        ->orWhereHas('institution', function ($institutionQuery) use ($keyword) {
                            $institutionQuery->where('name', 'like', $keyword);
                        });
                });
            })
            ->latest()
            ->paginate(10);

        $institutions = Institution::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $teachers = Teacher::query()
            ->where('is_active', true)
            ->when($this->institution_id, function ($query) {
                $query->whereHas('institutions', function ($q) {
                    $q->where('institutions.id', $this->institution_id);
                });
            })
            ->orderBy('name')
            ->get();

        return view('livewire.additional-honors.index', [
            'additionalHonors' => $additionalHonors,
            'institutions' => $institutions,
            'teachers' => $teachers,
        ])->layout('layouts.app');
    }
}

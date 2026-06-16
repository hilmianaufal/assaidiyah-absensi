<?php

namespace App\Livewire\Teachers;

use App\Models\Institution;
use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public array $institution_ids = [];
    public string $name = '';
    public string $nip = '';
    public string $phone = '';
    public int $hourly_rate = 25000;
    public bool $is_active = true;
    public bool $is_picket_officer = false;

    public bool $showModal = false;
    public ?int $editingId = null;

    public function updatedSearch(): void
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
        $teacher = Teacher::findOrFail($id);

        $this->editingId = $teacher->id;
        $this->name = $teacher->name;
        $this->nip = $teacher->nip ?? '';
        $this->phone = $teacher->phone ?? '';
        $this->hourly_rate = $teacher->hourly_rate;
        $this->is_active = (bool) $teacher->is_active;
        $this->is_picket_officer = (bool) $teacher->is_picket_officer;
        $this->institution_ids = $teacher
            ->institutions()
            ->pluck('institutions.id')
            ->toArray();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'hourly_rate' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_picket_officer' => ['boolean'],
        ]);

        $teacher = Teacher::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $teacher->institutions()->sync(
            $this->institution_ids
        );

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Teacher::findOrFail($id)->delete();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->institution_ids = [];
        $this->nip = '';
        $this->phone = '';
        $this->hourly_rate = 25000;
        $this->is_active = true;
        $this->is_picket_officer = false;
        $this->resetValidation();
    }

    public function render()
    {
        $teachers = Teacher::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('nip', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.teachers.index', [
    'teachers' => $teachers,
        'institutions' => Institution::orderBy('name')->get(),
    ])->layout('layouts.app');
    }
}

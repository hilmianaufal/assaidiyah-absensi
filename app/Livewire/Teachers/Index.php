<?php

namespace App\Livewire\Teachers;

use App\Models\Institution;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $nip = '';
    public string $phone = '';
    public int $hourly_rate = 25000;
    public bool $is_active = true;
    public bool $is_picket_officer = false;

    public array $institution_ids = [];

    protected string $paginationTheme = 'tailwind';

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
        $teacher = Teacher::with('institutions')->findOrFail($id);

        $this->editingId = $teacher->id;
        $this->name = $teacher->name;
        $this->nip = $teacher->nip ?? '';
        $this->phone = $teacher->phone ?? '';
        $this->hourly_rate = (int) $teacher->hourly_rate;
        $this->is_active = (bool) $teacher->is_active;
        $this->is_picket_officer = (bool) $teacher->is_picket_officer;

        $this->institution_ids = $teacher->institutions
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
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
            'institution_ids' => ['array'],
            'institution_ids.*' => ['exists:institutions,id'],
        ]);

        $institutionIds = $data['institution_ids'] ?? [];

        unset($data['institution_ids']);

        $teacher = Teacher::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $teacher->institutions()->sync($institutionIds);

        $this->showModal = false;
        $this->resetForm();

        $this->dispatch('success', message: 'Data guru berhasil disimpan.');
    }

    public function delete(int $id): void
    {
        $teacher = Teacher::findOrFail($id);

        $teacher->institutions()->detach();
        $teacher->delete();

        $this->resetPage();

        $this->dispatch('success', message: 'Data guru berhasil dihapus.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->nip = '';
        $this->phone = '';
        $this->hourly_rate = 25000;
        $this->is_active = true;
        $this->is_picket_officer = false;
        $this->institution_ids = [];

        $this->resetValidation();
    }

    public function render(): View
    {
        $teachers = Teacher::query()
            ->with('institutions')
            ->when(trim($this->search) !== '', function ($query) {
                $search = '%' . trim($this->search) . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhere('nip', 'like', $search)
                        ->orWhere('phone', 'like', $search);
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.teachers.index', [
            'teachers' => $teachers,
            'institutions' => Institution::query()
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}

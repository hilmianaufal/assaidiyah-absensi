<?php

namespace App\Livewire\Subjects;

use App\Models\Subject;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $name = '';
    public ?int $editingId = null;
    public bool $showModal = false;

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
        $subject = Subject::findOrFail($id);

        $this->editingId = $subject->id;
        $this->name = $subject->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Subject::updateOrCreate(
            ['id' => $this->editingId],
            ['name' => $this->name]
        );

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Subject::findOrFail($id)->delete();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->resetValidation();
    }

public function render()
{
    $subjects = Subject::query()
        ->when($this->search, fn ($query) =>
            $query->where('name', 'like', '%' . $this->search . '%')
        )
        ->latest()
        ->paginate(10);

    return view('livewire.subjects.index', [
        'subjects' => $subjects,
    ])->layout('layouts.app');
}
}
<?php

namespace App\Livewire\Users;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'admin';
    public string $teacher_id = '';

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
        $user = User::findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role ?? 'admin';
        $this->teacher_id = $user->teacher_id ? (string) $user->teacher_id : '';
        $this->password = '';

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $this->editingId],
            'role' => ['required', 'in:admin,guru'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
        ];

        if ($this->editingId) {
            $rules['password'] = ['nullable', 'string', 'min:6'];
        } else {
            $rules['password'] = ['required', 'string', 'min:6'];
        }

        $data = $this->validate($rules);

        if ($this->role === 'admin') {
            $data['teacher_id'] = null;
        }

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        } else {
            unset($data['password']);
        }

        User::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        if (auth()->id() === $id) {
            session()->flash('error', 'User yang sedang login tidak bisa dihapus.');
            return;
        }

        User::findOrFail($id)->delete();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'admin';
        $this->teacher_id = '';
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::with('teacher')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('role', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.users.index', [
            'users' => $users,
            'teachers' => Teacher::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
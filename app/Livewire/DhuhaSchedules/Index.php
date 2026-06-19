<?php

namespace App\Livewire\DhuhaSchedules;

use App\Models\DhuhaSchedule;
use App\Models\Institution;
use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $teacher_id = '';
    public string $institution_id = '';
    public string $day = '';
    public bool $is_active = true;

    public array $days = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Ahad',
    ];

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $schedule = DhuhaSchedule::findOrFail($id);

        $this->editingId = $schedule->id;
        $this->teacher_id = (string) $schedule->teacher_id;
        $this->institution_id = (string) $schedule->institution_id;
        $this->day = $schedule->day;
        $this->is_active = (bool) $schedule->is_active;

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'day' => ['required', 'string'],
            'is_active' => ['boolean'],
        ]);

        $data['institution_id'] = $data['institution_id'] ?: null;

        DhuhaSchedule::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->showModal = false;
        $this->resetForm();

        session()->flash('success', 'Jadwal petugas dhuha berhasil disimpan.');
    }

    public function delete(int $id): void
    {
        DhuhaSchedule::findOrFail($id)->delete();

        session()->flash('success', 'Jadwal petugas dhuha berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->teacher_id = '';
        $this->institution_id = '';
        $this->day = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.dhuha-schedules.index', [
            'schedules' => DhuhaSchedule::with(['teacher', 'institution'])
                ->latest()
                ->paginate(10),

            'teachers' => Teacher::where('is_active', true)
                ->orderBy('name')
                ->get(),

            'institutions' => Institution::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}

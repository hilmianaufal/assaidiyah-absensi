<?php

namespace App\Livewire\PicketSchedules;

use App\Models\Teacher;
use App\Models\TeacherPicketSchedule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $teacher_id = '';
    public string $day = '';
    public string $start_time = '07:00';
    public string $end_time = '12:00';
    public bool $is_active = true;

    public bool $showModal = false;
    public ?int $editingId = null;

    public array $days = [
        'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad',
    ];

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $schedule = TeacherPicketSchedule::findOrFail($id);

        $this->editingId = $schedule->id;
        $this->teacher_id = (string) $schedule->teacher_id;
        $this->day = $schedule->day;
        $this->start_time = substr($schedule->start_time, 0, 5);
        $this->end_time = substr($schedule->end_time, 0, 5);
        $this->is_active = (bool) $schedule->is_active;

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'day' => ['required', 'string'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'is_active' => ['boolean'],
        ]);

        TeacherPicketSchedule::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        TeacherPicketSchedule::findOrFail($id)->delete();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->teacher_id = '';
        $this->day = '';
        $this->start_time = '07:00';
        $this->end_time = '12:00';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $schedules = TeacherPicketSchedule::with('teacher')
            ->when($this->search, fn ($query) =>
                $query->whereHas('teacher', fn ($q) =>
                    $q->where('name', 'like', '%' . $this->search . '%')
                )
            )
            ->latest()
            ->paginate(10);

        return view('livewire.picket-schedules.index', [
            'schedules' => $schedules,
            'teachers' => Teacher::where('is_active', true)
                ->where('is_picket_officer', true)
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}
<?php

namespace App\Livewire\TeachingSchedules;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $day = '';

    public string $teacher_id = '';
    public string $subject_id = '';
    public string $class_name = '';
    public string $start_time = '';
    public string $end_time = '';
    public int $hours_count = 1;

    public ?int $editingId = null;
    public bool $showModal = false;

    public array $days = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Ahad',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDay(): void
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
        $schedule = TeachingSchedule::findOrFail($id);

        $this->editingId = $schedule->id;
        $this->teacher_id = (string) $schedule->teacher_id;
        $this->subject_id = (string) $schedule->subject_id;
        $this->class_name = $schedule->class_name;
        $this->day = $schedule->day;
        $this->start_time = substr($schedule->start_time, 0, 5);
        $this->end_time = substr($schedule->end_time, 0, 5);
        $this->hours_count = $schedule->hours_count;

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_name' => ['required', 'string', 'max:100'],
            'day' => ['required', 'string', 'max:20'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'hours_count' => ['required', 'integer', 'min:1'],
        ]);

        TeachingSchedule::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        TeachingSchedule::findOrFail($id)->delete();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->teacher_id = '';
        $this->subject_id = '';
        $this->class_name = '';
        $this->day = '';
        $this->start_time = '';
        $this->end_time = '';
        $this->hours_count = 1;
        $this->resetValidation();
    }

    public function render()
    {
        $schedules = TeachingSchedule::query()
            ->with(['teacher', 'subject'])
            ->when($this->search, function ($query) {
                $query->where('class_name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('teacher', fn ($q) =>
                        $q->where('name', 'like', '%' . $this->search . '%')
                    )
                    ->orWhereHas('subject', fn ($q) =>
                        $q->where('name', 'like', '%' . $this->search . '%')
                    );
            })
            ->when($this->day, fn ($query) =>
                $query->where('day', $this->day)
            )
            ->latest()
            ->paginate(10);

            return view('livewire.teaching-schedules.index', [
            'schedules' => $schedules,
            'teachers' => Teacher::where('is_active', true)->orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
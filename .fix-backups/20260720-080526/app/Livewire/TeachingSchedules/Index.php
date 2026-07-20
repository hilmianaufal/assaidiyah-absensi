<?php

namespace App\Livewire\TeachingSchedules;

use App\Models\Institution;
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

    public string $institution_id = '';
    public string $teacher_id = '';
    public string $subject_id = '';
    public string $class_name = '';
    public string $start_time = '';
    public string $end_time = '';
    public int $hours_count = 1;

    public ?int $editingId = null;
    public bool $showModal = false;
    public bool $showBulkModal = false;

    public string $bulk_day = '';

    public string $bulk_teacher_id = '';

    public array $scheduleRows = [];
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

    public function createBulk(): void
    {
        $this->bulk_day = '';
        $this->bulk_teacher_id = '';

        $this->scheduleRows = [
            [
                'institution_id' => '',
                'subject_id' => '',
                'class_name' => '',
                'start_time' => '',
                'end_time' => '',
                'hours_count' => 1,
            ]
        ];

        $this->showBulkModal = true;
    }

    public function addRow(): void
    {
        $this->scheduleRows[] = [
            'institution_id' => '',
            'subject_id' => '',
            'class_name' => '',
            'start_time' => '',
            'end_time' => '',
            'hours_count' => 1,
        ];
    }

    public function saveBulk(): void
    {
        $this->validate([
            'bulk_teacher_id' => [
                'required',
                'exists:teachers,id'
            ],

            'bulk_day' => [
                'required'
            ],
        ]);

        foreach ($this->scheduleRows as $row) {

            TeachingSchedule::create([
                'institution_id' => $row['institution_id'],
                'teacher_id' => $this->bulk_teacher_id,
                'subject_id' => $row['subject_id'],
                'class_name' => $row['class_name'],
                'day' => $this->bulk_day,
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'hours_count' => $row['hours_count'],
            ]);
        }

        $this->showBulkModal = false;

        session()->flash(
            'success',
            'Jadwal massal berhasil disimpan.'
        );
    }

    public function removeRow(int $index): void
    {
        unset($this->scheduleRows[$index]);

        $this->scheduleRows = array_values(
            $this->scheduleRows
        );
    }

    public function edit(int $id): void
    {
        $schedule = TeachingSchedule::findOrFail($id);

        $this->editingId = $schedule->id;
        $this->institution_id = (string) $schedule->institution_id;
        $this->teacher_id = (string) $schedule->teacher_id;
        $this->subject_id = (string) $schedule->subject_id;
        $this->class_name = $schedule->class_name;
        $this->day = $schedule->day;
        $this->start_time = substr($schedule->start_time, 0, 5);
        $this->end_time = substr($schedule->end_time, 0, 5);
        $this->hours_count = (int) $schedule->hours_count;

        $this->showModal = true;

    }

    public function save(): void
    {
        $data = $this->validate([
            'institution_id' => ['required', 'exists:institutions,id'],
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

        session()->flash('success', 'Jadwal mengajar berhasil disimpan.');
    }

    public function delete(int $id): void
    {
        TeachingSchedule::findOrFail($id)->delete();

        session()->flash('success', 'Jadwal mengajar berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->institution_id = '';
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
            ->with(['institution', 'teacher', 'subject'])
            ->when($this->search, function ($query) {
                $query->where('class_name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('teacher', fn ($q) =>
                        $q->where('name', 'like', '%' . $this->search . '%')
                    )
                    ->orWhereHas('subject', fn ($q) =>
                        $q->where('name', 'like', '%' . $this->search . '%')
                    )
                    ->orWhereHas('institution', fn ($q) =>
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
            'institutions' => Institution::where('is_active', true)->orderBy('name')->get(),
            'teachers' => Teacher::where('is_active', true)
                        ->when($this->institution_id, function ($query) {
                            $query->whereHas('institutions', function ($q) {
                                $q->where('institutions.id', $this->institution_id);
                            });
                        })
                        ->orderBy('name')
                        ->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ])->layout('layouts.app');
    }

    public function updatedInstitutionId(): void
    {
        $this->teacher_id = '';
    }
}

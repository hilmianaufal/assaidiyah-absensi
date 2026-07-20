<?php

namespace App\Livewire\SubjectAttendances;

use App\Exports\SubjectAttendancesExport;
use App\Models\SubjectAttendance;
use App\Models\TeachingSchedule;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $date = '';
    public string $teaching_schedule_id = '';
    public ?int $editingId = null;
    public bool $showModal = false;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDate(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->teaching_schedule_id = '';
        $this->editingId = null;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'teaching_schedule_id' => ['required', 'exists:teaching_schedules,id'],
        ]);

        $schedule = TeachingSchedule::with(['teacher', 'subject', 'institution'])
            ->findOrFail($this->teaching_schedule_id);

        $teachingHonor = $schedule->hours_count * $schedule->teacher->hourly_rate;

        SubjectAttendance::updateOrCreate(
            [
                'institution_id' => $schedule->institution_id,
                'teacher_id' => $schedule->teacher_id,
                'subject_id' => $schedule->subject_id,
                'teaching_schedule_id' => $schedule->id,
                'teaching_date' => now()->toDateString(),
            ],
            [
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'hours_count' => $schedule->hours_count,
                'hourly_rate' => $schedule->teacher->hourly_rate,
                'teaching_honor' => $teachingHonor,
                'class_name' => $schedule->class_name,
                'status' => 'present',
                'note' => 'Hadir mengajar mata pelajaran.',
            ]
        );

        $this->showModal = false;
        $this->teaching_schedule_id = '';
    }

    public function delete(int $id): void
    {
        SubjectAttendance::findOrFail($id)->delete();
    }

    public function render()
    {
        $attendances = SubjectAttendance::query()
            ->with(['teacher', 'subject'])
            ->when($this->date, fn ($query) =>
                $query->whereDate('teaching_date', $this->date)
            )
            ->when($this->search, function ($query) {
                $query->where('class_name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('teacher', fn ($q) =>
                        $q->where('name', 'like', '%' . $this->search . '%')
                    )
                    ->orWhereHas('subject', fn ($q) =>
                        $q->where('name', 'like', '%' . $this->search . '%')
                    );
            })
            ->latest()
            ->paginate(10);

        return view('livewire.subject-attendances.index', [
            'attendances' => $attendances,
            'schedules' => TeachingSchedule::with(['teacher', 'subject'])
                ->latest()
                ->get(),
        ])->layout('layouts.app');
    }

    public function exportExcel()
{
    return Excel::download(
        new SubjectAttendancesExport($this->date),
        'absensi-mapel-' . $this->date . '.xlsx'
    );
}
}

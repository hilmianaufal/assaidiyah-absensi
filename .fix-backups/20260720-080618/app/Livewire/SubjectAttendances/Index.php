<?php

namespace App\Livewire\SubjectAttendances;

use App\Exports\SubjectAttendancesExport;
use App\Models\SubjectAttendance;
use App\Models\TeacherHonorPackage;
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
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'teaching_schedule_id' => [
                'required',
                'exists:teaching_schedules,id',
            ],
            'date' => [
                'required',
                'date',
            ],
        ]);

        $schedule = TeachingSchedule::with([
            'teacher',
            'subject',
            'institution',
        ])->findOrFail($this->teaching_schedule_id);

        $package = TeacherHonorPackage::query()
            ->where(
                'teacher_id',
                $schedule->teacher_id
            )
            ->where(
                'institution_id',
                $schedule->institution_id
            )
            ->where('is_active', true)
            ->first();

        if (! $package) {
            $this->addError(
                'teaching_schedule_id',
                'Paket honor guru untuk lembaga ini belum dibuat atau belum aktif.'
            );

            return;
        }

        $ratePerHour = (int) $package->deduction_per_hour;
        $teachingHonor = (int) $schedule->hours_count
            * $ratePerHour;

        SubjectAttendance::updateOrCreate(
            [
                'teacher_id' => $schedule->teacher_id,
                'subject_id' => $schedule->subject_id,
                'teaching_schedule_id' => $schedule->id,
                'teaching_date' => $this->date,
            ],
            [
                'institution_id' => $schedule->institution_id,
                'recorded_by_teacher_id' => auth()
                    ->user()
                    ?->teacher
                    ?->id,
                'source' => 'admin',
                'attendance_status' => 'present',
                'recorded_at' => now(),
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'hours_count' => $schedule->hours_count,
                'hourly_rate' => $ratePerHour,
                'teaching_honor' => $teachingHonor,
                'class_name' => $schedule->class_name,
                'status' => 'present',
                'note' => 'Hadir mengajar. Dicatat oleh admin.',
            ]
        );

        $this->showModal = false;
        $this->teaching_schedule_id = '';
        $this->resetPage();

        session()->flash(
            'success',
            'Absensi mata pelajaran berhasil disimpan.'
        );
    }

    public function delete(int $id): void
    {
        SubjectAttendance::findOrFail($id)->delete();

        $this->resetPage();

        session()->flash(
            'success',
            'Absensi mata pelajaran berhasil dihapus.'
        );
    }

    public function exportExcel()
    {
        return Excel::download(
            new SubjectAttendancesExport($this->date),
            'absensi-mapel-'
                . $this->date
                . '.xlsx'
        );
    }

    public function render()
    {
        $search = trim($this->search);

        $attendances = SubjectAttendance::query()
            ->with([
                'teacher',
                'subject',
                'institution',
            ])
            ->when(
                filled($this->date),
                fn ($query) => $query->whereDate(
                    'teaching_date',
                    $this->date
                )
            )
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $keyword = '%' . $search . '%';

                    $query->where(
                        function ($subQuery) use ($keyword) {
                            $subQuery
                                ->where(
                                    'class_name',
                                    'like',
                                    $keyword
                                )
                                ->orWhereHas(
                                    'teacher',
                                    fn ($teacherQuery) => $teacherQuery
                                        ->where(
                                            'name',
                                            'like',
                                            $keyword
                                        )
                                )
                                ->orWhereHas(
                                    'subject',
                                    fn ($subjectQuery) => $subjectQuery
                                        ->where(
                                            'name',
                                            'like',
                                            $keyword
                                        )
                                )
                                ->orWhereHas(
                                    'institution',
                                    fn ($institutionQuery) => $institutionQuery
                                        ->where(
                                            'name',
                                            'like',
                                            $keyword
                                        )
                                );
                        }
                    );
                }
            )
            ->latest('teaching_date')
            ->latest('updated_at')
            ->paginate(10);

        return view(
            'livewire.subject-attendances.index',
            [
                'attendances' => $attendances,
                'schedules' => TeachingSchedule::query()
                    ->with([
                        'teacher',
                        'subject',
                        'institution',
                    ])
                    ->orderBy('day')
                    ->orderBy('start_time')
                    ->get(),
            ]
        )->layout('layouts.app');
    }
}

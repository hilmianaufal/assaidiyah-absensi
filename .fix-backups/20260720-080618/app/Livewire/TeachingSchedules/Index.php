<?php

namespace App\Livewire\TeachingSchedules;

use App\Models\Institution;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use Illuminate\Support\Facades\DB;
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

    public function updatedInstitutionId(): void
    {
        $this->teacher_id = '';
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
            $this->emptyScheduleRow(),
        ];
        $this->resetValidation();
        $this->showBulkModal = true;
    }

    public function addRow(): void
    {
        $this->scheduleRows[] = $this->emptyScheduleRow();
    }

    public function removeRow(int $index): void
    {
        if (count($this->scheduleRows) <= 1) {
            return;
        }

        unset($this->scheduleRows[$index]);

        $this->scheduleRows = array_values(
            $this->scheduleRows
        );
    }

    public function saveBulk(): void
    {
        $this->validate([
            'bulk_teacher_id' => [
                'required',
                'exists:teachers,id',
            ],
            'bulk_day' => [
                'required',
                'in:' . implode(',', $this->days),
            ],
            'scheduleRows' => [
                'required',
                'array',
                'min:1',
            ],
            'scheduleRows.*.institution_id' => [
                'required',
                'exists:institutions,id',
            ],
            'scheduleRows.*.subject_id' => [
                'required',
                'exists:subjects,id',
            ],
            'scheduleRows.*.class_name' => [
                'required',
                'string',
                'max:100',
            ],
            'scheduleRows.*.start_time' => [
                'required',
                'date_format:H:i',
            ],
            'scheduleRows.*.end_time' => [
                'required',
                'date_format:H:i',
            ],
            'scheduleRows.*.hours_count' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        foreach ($this->scheduleRows as $index => $row) {
            if ($row['end_time'] <= $row['start_time']) {
                $this->addError(
                    "scheduleRows.$index.end_time",
                    'Jam selesai harus setelah jam mulai.'
                );

                return;
            }

            $teacherHasInstitution = Teacher::query()
                ->whereKey($this->bulk_teacher_id)
                ->whereHas(
                    'institutions',
                    fn ($query) => $query->where(
                        'institutions.id',
                        $row['institution_id']
                    )
                )
                ->exists();

            if (! $teacherHasInstitution) {
                $this->addError(
                    "scheduleRows.$index.institution_id",
                    'Guru belum terhubung dengan lembaga ini.'
                );

                return;
            }
        }

        DB::transaction(function (): void {
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
        });

        $this->showBulkModal = false;
        $this->bulk_day = '';
        $this->bulk_teacher_id = '';
        $this->scheduleRows = [];
        $this->resetPage();

        session()->flash(
            'success',
            'Jadwal massal berhasil disimpan.'
        );
    }

    public function edit(int $id): void
    {
        $schedule = TeachingSchedule::findOrFail($id);

        $this->editingId = $schedule->id;
        $this->institution_id = (string) $schedule
            ->institution_id;
        $this->teacher_id = (string) $schedule->teacher_id;
        $this->subject_id = (string) $schedule->subject_id;
        $this->class_name = (string) $schedule->class_name;
        $this->day = (string) $schedule->day;
        $this->start_time = substr(
            (string) $schedule->start_time,
            0,
            5
        );
        $this->end_time = substr(
            (string) $schedule->end_time,
            0,
            5
        );
        $this->hours_count = (int) $schedule->hours_count;

        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'institution_id' => [
                'required',
                'exists:institutions,id',
            ],
            'teacher_id' => [
                'required',
                'exists:teachers,id',
            ],
            'subject_id' => [
                'required',
                'exists:subjects,id',
            ],
            'class_name' => [
                'required',
                'string',
                'max:100',
            ],
            'day' => [
                'required',
                'in:' . implode(',', $this->days),
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'hours_count' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $teacherHasInstitution = Teacher::query()
            ->whereKey($this->teacher_id)
            ->whereHas(
                'institutions',
                fn ($query) => $query->where(
                    'institutions.id',
                    $this->institution_id
                )
            )
            ->exists();

        if (! $teacherHasInstitution) {
            $this->addError(
                'teacher_id',
                'Guru belum terhubung dengan lembaga yang dipilih.'
            );

            return;
        }

        TeachingSchedule::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();

        session()->flash(
            'success',
            'Jadwal mengajar berhasil disimpan.'
        );
    }

    public function delete(int $id): void
    {
        TeachingSchedule::findOrFail($id)->delete();

        $this->resetPage();

        session()->flash(
            'success',
            'Jadwal mengajar berhasil dihapus.'
        );
    }

    private function emptyScheduleRow(): array
    {
        return [
            'institution_id' => '',
            'subject_id' => '',
            'class_name' => '',
            'start_time' => '',
            'end_time' => '',
            'hours_count' => 1,
        ];
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
        $search = trim($this->search);

        $schedules = TeachingSchedule::query()
            ->with([
                'institution',
                'teacher',
                'subject',
            ])
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
            ->when(
                filled($this->day),
                fn ($query) => $query->where(
                    'day',
                    $this->day
                )
            )
            ->latest()
            ->paginate(10);

        return view(
            'livewire.teaching-schedules.index',
            [
                'schedules' => $schedules,
                'institutions' => Institution::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),
                'teachers' => Teacher::query()
                    ->where('is_active', true)
                    ->when(
                        filled($this->institution_id),
                        function ($query) {
                            $query->whereHas(
                                'institutions',
                                fn ($institutionQuery) => $institutionQuery
                                    ->where(
                                        'institutions.id',
                                        $this->institution_id
                                    )
                            );
                        }
                    )
                    ->orderBy('name')
                    ->get(),
                'subjects' => Subject::query()
                    ->orderBy('name')
                    ->get(),
            ]
        )->layout('layouts.app');
    }
}

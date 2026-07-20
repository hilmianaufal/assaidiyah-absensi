<?php

namespace App\Livewire\DailyAttendances;

use App\Exports\DailyAttendancesExport;
use App\Models\DailyAttendance;
use App\Models\Teacher;
use App\Services\TeacherAttendanceService;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $date = '';
    public string $teacher_id = '';

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

    public function openAttendance(): void
    {
        $this->teacher_id = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveAttendance(): void
    {
        $this->validate([
            'teacher_id' => [
                'required',
                'exists:teachers,id',
            ],
        ]);

        $teacher = Teacher::query()
            ->where('is_active', true)
            ->findOrFail($this->teacher_id);

        $result = app(TeacherAttendanceService::class)
            ->record($teacher, 'check_in');

        $this->showModal = false;
        $this->teacher_id = '';
        $this->resetPage();

        if (($result['status'] ?? null) === 'success') {
            session()->flash(
                'success',
                $result['message']
            );

            return;
        }

        session()->flash(
            'error',
            $result['message']
                ?? 'Absensi tidak dapat disimpan.'
        );
    }

    public function delete(int $id): void
    {
        DailyAttendance::findOrFail($id)->delete();

        $this->resetPage();

        session()->flash(
            'success',
            'Data absensi berhasil dihapus.'
        );
    }

    public function exportExcel()
    {
        return Excel::download(
            new DailyAttendancesExport($this->date),
            'absensi-harian-'
                . $this->date
                . '.xlsx'
        );
    }

    public function render()
    {
        $attendances = DailyAttendance::query()
            ->with('teacher')
            ->when(
                filled($this->date),
                fn ($query) => $query->whereDate(
                    'attendance_date',
                    $this->date
                )
            )
            ->when(
                filled(trim($this->search)),
                function ($query) {
                    $keyword = '%'
                        . trim($this->search)
                        . '%';

                    $query->whereHas(
                        'teacher',
                        fn ($teacherQuery) => $teacherQuery
                            ->where('name', 'like', $keyword)
                    );
                }
            )
            ->latest('attendance_date')
            ->latest('updated_at')
            ->paginate(10);

        return view(
            'livewire.daily-attendances.index',
            [
                'attendances' => $attendances,
                'teachers' => Teacher::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),
            ]
        )->layout('layouts.app');
    }
}

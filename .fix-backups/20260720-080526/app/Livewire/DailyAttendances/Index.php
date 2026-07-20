<?php

namespace App\Livewire\DailyAttendances;

use App\Exports\DailyAttendancesExport;
use App\Models\DailyAttendance;
use App\Models\Teacher;
use Carbon\Carbon;
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
        $this->showModal = true;
    }

    public function saveAttendance(): void
    {
        $this->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
        ]);

        $now = now();
        $time = $now->format('H:i:s');

        $start = Carbon::createFromTimeString('06:45:00');
        $end = Carbon::createFromTimeString('07:15:00');

        $isTransport = $now->betweenIncluded($start, $end);

        DailyAttendance::updateOrCreate(
            [
                'teacher_id' => $this->teacher_id,
                'attendance_date' => $now->toDateString(),
            ],
            [
                'attendance_time' => $time,
                'status' => 'present',
                'transport_amount' => $isTransport ? 10000 : 0,
                'note' => $isTransport
                    ? 'Hadir tepat waktu, mendapat transport.'
                    : 'Hadir di luar waktu transport, hanya menggugurkan kewajiban.',
            ]
        );

        $this->showModal = false;
        $this->teacher_id = '';
    }

    public function delete(int $id): void
    {
        DailyAttendance::findOrFail($id)->delete();
    }

    public function render()
    {
        $attendances = DailyAttendance::query()
            ->with('teacher')
            ->when($this->date, fn ($query) =>
                $query->whereDate('attendance_date', $this->date)
            )
            ->when($this->search, fn ($query) =>
                $query->whereHas('teacher', fn ($q) =>
                    $q->where('name', 'like', '%' . $this->search . '%')
                )
            )
            ->latest()
            ->paginate(10);

        return view('livewire.daily-attendances.index', [
            'attendances' => $attendances,
            'teachers' => Teacher::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }

    public function exportExcel()
{
    return Excel::download(
        new DailyAttendancesExport($this->date),
        'absensi-harian-' . $this->date . '.xlsx'
    );
}
}
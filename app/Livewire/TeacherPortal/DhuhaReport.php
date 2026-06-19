<?php

namespace App\Livewire\TeacherPortal;

use App\Models\DhuhaReport as DhuhaReportModel;
use App\Models\DhuhaSchedule;
use App\Models\Teacher;
use Livewire\Component;

class DhuhaReport extends Component
{
    public string $status = 'done';
    public array $present_teacher_ids = [];
    public string $note = '';
    public string $searchTeacher = '';

    public ?DhuhaSchedule $todaySchedule = null;
    public ?DhuhaReportModel $existingReport = null;

    public function mount(): void
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            abort(403, 'Akun belum terhubung dengan data guru.');
        }

        $this->todaySchedule = DhuhaSchedule::with('teacher')
            ->where('teacher_id', $teacher->id)
            ->where('day', $this->currentDayName())
            ->where('is_active', true)
            ->first();

        if ($this->todaySchedule) {
            $this->existingReport = DhuhaReportModel::where('teacher_id', $teacher->id)
                ->whereNull('institution_id')
                ->whereDate('report_date', now()->toDateString())
                ->first();

            if ($this->existingReport) {
                $this->status = $this->existingReport->status;
                $this->present_teacher_ids = $this->existingReport->present_teacher_ids ?? [];
                $this->note = (string) $this->existingReport->note;
            }
        }
    }

    private function currentDayName(): string
    {
        return [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Ahad',
        ][now()->format('l')];
    }

    public function whatsappMessage(): string
    {
        $teacher = auth()->user()->teacher;

        $presentTeachers = Teacher::whereIn('id', $this->present_teacher_ids)
            ->orderBy('name')
            ->get();

        $teacherList = $presentTeachers->isNotEmpty()
            ? $presentTeachers->values()->map(
                fn ($item, $index) => ($index + 1) . '. ' . $item->name
            )->implode("\n")
            : '-';

        $statusText = $this->status === 'done'
            ? 'Terlaksana'
            : 'Tidak Terlaksana';

        return "📢 *LAPORAN SHOLAT DHUHA*\n\n"
            . "Assalamu’alaikum Wr. Wb.\n\n"
            . "Hari/Tanggal : " . now()->locale('id')->translatedFormat('l, d F Y') . "\n"
            . "Pelapor      : {$teacher?->name}\n"
            . "Status       : {$statusText}\n\n"
            . "*Guru Hadir:*\n"
            . "{$teacherList}\n\n"
            . "Jumlah Hadir : " . $presentTeachers->count() . " guru\n\n"
            . "Keterangan:\n"
            . ($this->note ?: '-') . "\n\n"
            . "Wassalamu’alaikum Wr. Wb.";
    }

    public function save(): void
    {
        if (! $this->todaySchedule) {
            abort(403, 'Anda tidak mendapat jadwal laporan dhuha hari ini.');
        }

        $this->validate([
            'status' => ['required', 'in:done,not_done'],
            'present_teacher_ids' => ['array'],
            'present_teacher_ids.*' => ['exists:teachers,id'],
            'note' => ['nullable', 'string'],
        ]);

        $teacher = auth()->user()->teacher;
        $message = $this->whatsappMessage();

        $this->existingReport = DhuhaReportModel::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'institution_id' => null,
                'report_date' => now()->toDateString(),
            ],
            [
                'status' => $this->status,
                'present_teacher_ids' => $this->present_teacher_ids,
                'teacher_count' => count($this->present_teacher_ids),
                'imam_name' => null,
                'note' => $this->note,
                'whatsapp_message' => $message,
            ]
        );

        session()->flash('success', 'Laporan dhuha berhasil disimpan.');
    }

    public function render()
    {
        $teachers = Teacher::where('is_active', true)
            ->when($this->searchTeacher, function ($query) {
                $query->where('name', 'like', '%' . $this->searchTeacher . '%');
            })
            ->orderBy('name')
            ->get();

        return view('livewire.teacher-portal.dhuha-report', [
            'teachers' => $teachers,
            'waMessage' => $this->whatsappMessage(),
            'waUrl' => 'https://wa.me/?text=' . urlencode($this->whatsappMessage()),
            'indonesianDate' => now()->locale('id')->translatedFormat('l, d F Y'),
        ])->layout('layouts.app');
    }
}

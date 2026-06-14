<?php

namespace App\Livewire\PicketReports;

use App\Models\PicketReport;
use App\Models\PicketReportStudent;
use App\Models\Teacher;
use App\Models\TeacherPicketSchedule;
use App\Services\WhatsappService;
use Livewire\Component;

class Create extends Component
{
    public string $teacher_absences = '';
    public string $class_name = '';
    public string $student_name = '';
    public string $status = 'alpa';

    public array $students = [];

    public ?Teacher $teacher = null;
    public bool $isAllowed = false;
    public string $dayName = '';

    public array $classes = [
        'Kelas 10',
        'Kelas 11 Paket 1',
        'Kelas 11 Paket 2',
        'Kelas 12 Paket 1',
        'Kelas 12 Paket 2',
    ];

    public function mount(): void
    {
        $this->teacher = auth()->user()->teacher ?? null;
        $this->dayName = $this->currentDayName();

        if (! $this->teacher || ! $this->teacher->is_picket_officer) {
            $this->isAllowed = false;
            return;
        }

        $this->isAllowed = TeacherPicketSchedule::where('teacher_id', $this->teacher->id)
            ->where('day', $this->dayName)
            ->where('is_active', true)
            ->exists();
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

    public function addStudent(): void
    {
        $this->validate([
            'class_name' => ['required', 'string'],
            'student_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string'],
        ]);

        $this->students[] = [
            'class_name' => $this->class_name,
            'student_name' => $this->student_name,
            'status' => $this->status,
        ];

        $this->student_name = '';
        $this->status = 'alpa';
    }

    public function removeStudent(int $index): void
    {
        unset($this->students[$index]);
        $this->students = array_values($this->students);
    }

    public function buildMessage(): string
    {
        $date = now()->locale('id')->translatedFormat('l, d F Y');
        $teacherAbsences = trim($this->teacher_absences) ?: '-';

        $message = "Bismillahirrahmanirrahim...\n\n";
        $message .= "📅 {$date}\n";
        $message .= "📌 Guru Piket: {$this->teacher?->name}\n\n";
        $message .= "🔖 Guru yang berhalangan hadir :\n";
        $message .= "{$teacherAbsences}\n\n";
        $message .= "📝 Siswa yang tidak masuk :\n\n";

        foreach ($this->classes as $class) {
            $message .= "🏘️ {$class}\n";

            $items = collect($this->students)
                ->where('class_name', $class)
                ->values();

            if ($items->isEmpty()) {
                $message .= "Berangkat semua\n\n";
                continue;
            }

            foreach ($items as $i => $student) {
                $no = $i + 1;
                $message .= "{$no}. {$student['student_name']} ({$student['status']})\n";
            }

            $message .= "\n";
        }

        $message .= "🌹 Terima kasih🌹\n\n";
        $message .= "Absen Online ✅";

        return $message;
    }

    public function save(): void
    {
        if (! $this->isAllowed) {
            abort(403);
        }

        $report = PicketReport::updateOrCreate(
            [
                'teacher_id' => $this->teacher->id,
                'report_date' => now()->toDateString(),
            ],
            [
                'teacher_absences' => $this->teacher_absences,
                'whatsapp_message' => $this->buildMessage(),
            ]
        );

        $report->students()->delete();

        foreach ($this->students as $student) {
            PicketReportStudent::create([
                'picket_report_id' => $report->id,
                'class_name' => $student['class_name'],
                'student_name' => $student['student_name'],
                'status' => $student['status'],
            ]);
        }

        session()->flash('success', 'Laporan piket berhasil disimpan.');
    }

    public function sendWhatsappGroup(): void
    {
        if (! $this->isAllowed) {
            abort(403);
        }

        $message = $this->buildMessage();

        $response = WhatsappService::send(
            env('WA_GROUP_TARGET'),
            $message
        );

        if ($response->successful()) {
            PicketReport::updateOrCreate(
                [
                    'teacher_id' => $this->teacher->id,
                    'report_date' => now()->toDateString(),
                ],
                [
                    'teacher_absences' => $this->teacher_absences,
                    'whatsapp_message' => $message,
                    'sent_at' => now(),
                ]
            );

            session()->flash('success', 'Laporan berhasil dikirim ke WhatsApp.');
        } else {
            session()->flash('error', 'Gagal kirim WhatsApp. Cek token atau target WA.');
        }
    }

    public function render()
    {
        return view('livewire.picket-reports.create', [
            'previewMessage' => $this->buildMessage(),
        ])->layout('layouts.app');
    }
}

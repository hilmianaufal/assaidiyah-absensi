<?php

namespace App\Livewire\PicketSubjectAttendances;

use App\Models\SubjectAttendance;
use App\Models\TeacherHonorPackage;
use App\Models\TeacherPicketSchedule;
use App\Models\TeachingSchedule;
use Illuminate\Support\Facades\Request;
use Livewire\Component;

class Index extends Component
{
    public string $dayName = '';
    public bool $isAllowed = false;

    public ?int $picketInstitutionId = null;
    public ?TeacherPicketSchedule $picketSchedule = null;

    public function mount(): void
    {
        $teacher = auth()->user()->teacher;

        $this->dayName = $this->currentDayName();

        if (! $teacher || ! $teacher->is_picket_officer) {
            $this->isAllowed = false;
            return;
        }

        $this->picketSchedule = TeacherPicketSchedule::with('institution')
            ->where('teacher_id', $teacher->id)
            ->where('day', $this->dayName)
            ->where('is_active', true)
            ->first();

        if (! $this->picketSchedule) {
            $this->isAllowed = false;
            return;
        }

        $this->picketInstitutionId = $this->picketSchedule->institution_id;
        $this->isAllowed = true;
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

    public function markPicketSubjectAttendance(Request $request)
{
    $teacher = $request->user()->teacher;

    $data = $request->validate([
        'teaching_schedule_id' => ['required', 'exists:teaching_schedules,id'],
        'status' => ['required', 'in:present,late,permit,sick,absent'],
    ]);

    $day = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Ahad',
    ][now()->format('l')];

    $picketSchedule = TeacherPicketSchedule::where('teacher_id', $teacher->id)
        ->where('day', $day)
        ->where('is_active', true)
        ->first();

    if (! $teacher->is_picket_officer || ! $picketSchedule) {
        return response()->json([
            'message' => 'Anda tidak memiliki akses piket hari ini.',
        ], 403);
    }

    $schedule = TeachingSchedule::with(['teacher', 'subject', 'institution'])
        ->where('institution_id', $picketSchedule->institution_id)
        ->findOrFail($data['teaching_schedule_id']);

    $oldAttendance = SubjectAttendance::where([
        'teacher_id' => $schedule->teacher_id,
        'subject_id' => $schedule->subject_id,
        'teaching_schedule_id' => $schedule->id,
        'teaching_date' => now()->toDateString(),
    ])->first();

    $oldStatus = $oldAttendance?->attendance_status;

    $isPaid = in_array($data['status'], ['present', 'late']);

    $package = TeacherHonorPackage::where('teacher_id', $schedule->teacher_id)
        ->where('institution_id', $schedule->institution_id)
        ->where('is_active', true)
        ->first();

    $ratePerHour = $package?->deduction_per_hour ?? 0;

    $teachingHonor = $isPaid
        ? ($schedule->hours_count * $ratePerHour)
        : 0;

    $attendance = SubjectAttendance::updateOrCreate(
        [
            'teacher_id' => $schedule->teacher_id,
            'subject_id' => $schedule->subject_id,
            'teaching_schedule_id' => $schedule->id,
            'teaching_date' => now()->toDateString(),
        ],
        [
            'institution_id' => $schedule->institution_id,
            'recorded_by_teacher_id' => $teacher->id,
            'source' => 'android_picket',
            'attendance_status' => $data['status'],
            'recorded_at' => now(),

            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'hours_count' => $schedule->hours_count,
            'hourly_rate' => $ratePerHour,
            'teaching_honor' => $teachingHonor,
            'class_name' => $schedule->class_name,
            'status' => $data['status'],
            'note' => 'Dicatat dari aplikasi Android oleh guru piket: ' . $teacher->name,
        ]
    );

    if ($oldStatus !== $data['status']) {
        $statusText = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'permit' => 'Izin',
            'sick' => 'Sakit',
            'absent' => 'Alpa',
        ][$data['status']] ?? $data['status'];

        \App\Models\AppNotification::create([
            'teacher_id' => $schedule->teacher_id,
            'title' => 'Absensi mapel dicatat',
            'message' => 'Status mengajar Anda pada mapel ' .
                $schedule->subject->name .
                ' kelas ' .
                $schedule->class_name .
                ' dicatat: ' .
                $statusText .
                '.',
            'type' => in_array($data['status'], ['present', 'late'])
                ? 'success'
                : 'warning',
        ]);

        if ($teachingHonor > 0) {
            \App\Models\AppNotification::create([
                'teacher_id' => $schedule->teacher_id,
                'title' => 'Honor berjalan bertambah',
                'message' => 'Honor mengajar Anda bertambah Rp ' .
                    number_format($teachingHonor, 0, ',', '.') .
                    ' dari mapel ' .
                    $schedule->subject->name .
                    '.',
                'type' => 'success',
            ]);
        }
    }

    return response()->json([
        'message' => 'Absensi mapel berhasil disimpan.',
        'attendance' => $attendance,
    ]);
}

    public function render()
    {
        $schedules = collect();

        if ($this->isAllowed && $this->picketInstitutionId) {
            $schedules = TeachingSchedule::with(['teacher', 'subject', 'institution'])
                ->where('day', $this->dayName)
                ->where('institution_id', $this->picketInstitutionId)
                ->orderBy('start_time')
                ->get();
        }

        $attendances = SubjectAttendance::whereDate('teaching_date', now()->toDateString())
            ->when($this->picketInstitutionId, fn ($query) =>
                $query->where('institution_id', $this->picketInstitutionId)
            )
            ->get()
            ->keyBy('teaching_schedule_id');

        return view('livewire.picket-subject-attendances.index', [
            'schedules' => $schedules,
            'attendances' => $attendances,
            'picketSchedule' => $this->picketSchedule,
        ])->layout('layouts.app');
    }

    public function markAttendance(int $scheduleId, string $status): void
{
    $teacher = auth()->user()->teacher;

    $schedule = TeachingSchedule::with(['teacher', 'subject', 'institution'])
        ->where('institution_id', $this->picketInstitutionId)
        ->findOrFail($scheduleId);

    $isPaid = in_array($status, ['present', 'late']);

    $package = TeacherHonorPackage::where('teacher_id', $schedule->teacher_id)
        ->where('institution_id', $schedule->institution_id)
        ->where('is_active', true)
        ->first();

    $ratePerHour = $package?->deduction_per_hour ?? 0;

    $teachingHonor = $isPaid
        ? ($schedule->hours_count * $ratePerHour)
        : 0;

    SubjectAttendance::updateOrCreate(
        [
            'teacher_id' => $schedule->teacher_id,
            'subject_id' => $schedule->subject_id,
            'teaching_schedule_id' => $schedule->id,
            'teaching_date' => now()->toDateString(),
        ],
        [
            'institution_id' => $schedule->institution_id,
            'recorded_by_teacher_id' => $teacher->id,
            'source' => 'picket',
            'attendance_status' => $status,
            'recorded_at' => now(),

            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'hours_count' => $schedule->hours_count,
            'hourly_rate' => $ratePerHour,
            'teaching_honor' => $teachingHonor,
            'class_name' => $schedule->class_name,
            'status' => $status,
            'note' => 'Dicatat oleh guru piket: '.$teacher->name,
        ]
    );

    session()->flash('success', 'Absensi berhasil disimpan.');
}
}

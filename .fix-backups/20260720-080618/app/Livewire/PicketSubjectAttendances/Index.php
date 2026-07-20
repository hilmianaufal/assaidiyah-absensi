<?php

namespace App\Livewire\PicketSubjectAttendances;

use App\Models\AppNotification;
use App\Models\SubjectAttendance;
use App\Models\TeacherHonorPackage;
use App\Models\TeacherPicketSchedule;
use App\Models\TeachingSchedule;
use Livewire\Component;

class Index extends Component
{
    public string $dayName = '';
    public bool $isAllowed = false;

    public ?int $picketInstitutionId = null;
    public ?TeacherPicketSchedule $picketSchedule = null;

    public function mount(): void
    {
        $this->dayName = $this->currentDayName();
        $this->loadAccess();
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

    private function loadAccess(): void
    {
        $teacher = auth()->user()?->teacher;

        $this->isAllowed = false;
        $this->picketInstitutionId = null;
        $this->picketSchedule = null;

        if (
            ! $teacher
            || ! $teacher->is_picket_officer
        ) {
            return;
        }

        $schedule = TeacherPicketSchedule::with(
            'institution'
        )
            ->where('teacher_id', $teacher->id)
            ->where('day', $this->dayName)
            ->where('is_active', true)
            ->first();

        if (! $schedule) {
            return;
        }

        $this->picketSchedule = $schedule;
        $this->picketInstitutionId = (int) $schedule
            ->institution_id;
        $this->isAllowed = true;
    }

    public function markAttendance(
        int $scheduleId,
        string $status
    ): void {
        $allowedStatuses = [
            'present',
            'late',
            'permit',
            'sick',
            'absent',
        ];

        if (! in_array(
            $status,
            $allowedStatuses,
            true
        )) {
            session()->flash(
                'error',
                'Status absensi tidak valid.'
            );

            return;
        }

        $this->loadAccess();

        $picketTeacher = auth()->user()?->teacher;

        if (
            ! $this->isAllowed
            || ! $picketTeacher
            || ! $this->picketInstitutionId
        ) {
            session()->flash(
                'error',
                'Anda tidak memiliki akses piket hari ini.'
            );

            return;
        }

        $schedule = TeachingSchedule::with([
            'teacher',
            'subject',
            'institution',
        ])
            ->where(
                'institution_id',
                $this->picketInstitutionId
            )
            ->where('day', $this->dayName)
            ->findOrFail($scheduleId);

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
            session()->flash(
                'error',
                'Paket honor guru '
                    . $schedule->teacher->name
                    . ' pada lembaga ini belum aktif.'
            );

            return;
        }

        $oldAttendance = SubjectAttendance::query()
            ->where(
                'teacher_id',
                $schedule->teacher_id
            )
            ->where(
                'subject_id',
                $schedule->subject_id
            )
            ->where(
                'teaching_schedule_id',
                $schedule->id
            )
            ->whereDate(
                'teaching_date',
                now()->toDateString()
            )
            ->first();

        $oldStatus = $oldAttendance
            ?->attendance_status
            ?? $oldAttendance
            ?->status;

        $isPaid = in_array(
            $status,
            ['present', 'late'],
            true
        );

        $ratePerHour = (int) $package
            ->deduction_per_hour;

        $teachingHonor = $isPaid
            ? (int) $schedule->hours_count
                * $ratePerHour
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
                'recorded_by_teacher_id' => $picketTeacher->id,
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
                'note' => 'Dicatat oleh guru piket: '
                    . $picketTeacher->name,
            ]
        );

        if ($oldStatus !== $status) {
            $this->createNotifications(
                $schedule,
                $status,
                $teachingHonor
            );
        }

        session()->flash(
            'success',
            'Absensi '
                . $schedule->teacher->name
                . ' berhasil disimpan.'
        );
    }

    private function createNotifications(
        TeachingSchedule $schedule,
        string $status,
        int $teachingHonor
    ): void {
        $statusText = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'permit' => 'Izin',
            'sick' => 'Sakit',
            'absent' => 'Alpa',
        ][$status] ?? $status;

        AppNotification::create([
            'teacher_id' => $schedule->teacher_id,
            'title' => 'Absensi mapel dicatat',
            'message' => 'Status mengajar Anda pada mapel '
                . $schedule->subject->name
                . ' kelas '
                . $schedule->class_name
                . ' dicatat: '
                . $statusText
                . '.',
            'type' => in_array(
                $status,
                ['present', 'late'],
                true
            )
                ? 'success'
                : 'warning',
        ]);

        if ($teachingHonor <= 0) {
            return;
        }

        AppNotification::create([
            'teacher_id' => $schedule->teacher_id,
            'title' => 'Honor berjalan bertambah',
            'message' => 'Honor mengajar Anda bertambah Rp '
                . number_format(
                    $teachingHonor,
                    0,
                    ',',
                    '.'
                )
                . ' dari mapel '
                . $schedule->subject->name
                . '.',
            'type' => 'success',
        ]);
    }

    public function render()
    {
        $schedules = collect();
        $attendances = collect();

        if (
            $this->isAllowed
            && $this->picketInstitutionId
        ) {
            $schedules = TeachingSchedule::with([
                'teacher',
                'subject',
                'institution',
            ])
                ->where('day', $this->dayName)
                ->where(
                    'institution_id',
                    $this->picketInstitutionId
                )
                ->orderBy('start_time')
                ->get();

            $attendances = SubjectAttendance::query()
                ->whereDate(
                    'teaching_date',
                    now()->toDateString()
                )
                ->where(
                    'institution_id',
                    $this->picketInstitutionId
                )
                ->get()
                ->keyBy('teaching_schedule_id');
        }

        return view(
            'livewire.picket-subject-attendances.index',
            [
                'schedules' => $schedules,
                'attendances' => $attendances,
                'picketSchedule' => $this->picketSchedule,
            ]
        )->layout('layouts.app');
    }
}

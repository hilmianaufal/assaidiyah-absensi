#!/usr/bin/env bash
set -euo pipefail

if [ ! -f artisan ]; then
    echo "ERROR: Jalankan script ini dari folder utama project Laravel."
    exit 1
fi

BACKUP_DIR=".fix-backups/$(date +%Y%m%d-%H%M%S)"

backup_file() {
    local source_file="$1"

    if [ -f "$source_file" ]; then
        mkdir -p "$BACKUP_DIR/$(dirname "$source_file")"
        cp "$source_file" "$BACKUP_DIR/$source_file"
    fi
}

echo "Membuat backup file lama..."
backup_file "app/Services/TeacherAttendanceService.php"
backup_file "app/Livewire/FaceAttendance/Index.php"
backup_file "app/Livewire/Kiosk/Index.php"
backup_file "app/Livewire/DailyAttendances/Index.php"
backup_file "app/Livewire/SubjectAttendances/Index.php"
backup_file "app/Livewire/PicketSubjectAttendances/Index.php"
backup_file "app/Livewire/TeachingSchedules/Index.php"

echo "Menulis file perbaikan..."

mkdir -p "app/Services"
cat > "app/Services/TeacherAttendanceService.php" <<'PHP_FILE_1'
<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\DailyAttendance;
use App\Models\Teacher;
use App\Models\TransportSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class TeacherAttendanceService
{
    public function record(
        Teacher $teacher,
        string $mode,
        ?string $photoBase64 = null
    ): array {
        if (! in_array($mode, ['check_in', 'check_out'], true)) {
            throw new InvalidArgumentException('Mode absensi tidak valid.');
        }

        $now = now();

        $attendance = DailyAttendance::firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'attendance_date' => $now->toDateString(),
            ],
            [
                'status' => 'present',
                'transport_amount' => 0,
            ]
        );

        return $mode === 'check_in'
            ? $this->recordCheckIn($attendance, $teacher, $now, $photoBase64)
            : $this->recordCheckOut($attendance, $teacher, $now, $photoBase64);
    }

    private function recordCheckIn(
        DailyAttendance $attendance,
        Teacher $teacher,
        Carbon $now,
        ?string $photoBase64
    ): array {
        if ($attendance->check_in_time) {
            return [
                'status' => 'already',
                'type' => 'check_in',
                'name' => $teacher->name,
                'transport' => (int) $attendance->transport_amount,
                'message' => $teacher->name
                    . ' sudah absen masuk pukul '
                    . substr((string) $attendance->check_in_time, 0, 5)
                    . '.',
            ];
        }

        $setting = $this->activeSetting();

        $isValidCheckIn = $setting
            ? $this->isWithinWindow(
                $now,
                (string) $setting->check_in_start,
                (string) $setting->check_in_end
            )
            : false;

        $photoPath = $this->saveBase64Photo(
            $photoBase64,
            'attendance/check-in'
        );

        $attendance->update([
            'attendance_time' => $now->format('H:i:s'),
            'check_in_time' => $now->format('H:i:s'),
            'check_in_status' => $isValidCheckIn ? 'ontime' : 'late',
            'check_in_photo' => $photoPath,
            'status' => 'present',
            'transport_amount' => 0,
            'note' => $setting
                ? (
                    $isValidCheckIn
                        ? 'Absen masuk sesuai jadwal. Transport menunggu absen pulang.'
                        : 'Absen masuk di luar jadwal transport.'
                )
                : 'Absen masuk tercatat, tetapi pengaturan transport belum aktif.',
        ]);

        return [
            'status' => 'success',
            'type' => 'check_in',
            'name' => $teacher->name,
            'check_in_status' => $isValidCheckIn ? 'ontime' : 'late',
            'transport' => 0,
            'photo' => $photoPath,
            'message' => $teacher->name
                . ' berhasil absen masuk pukul '
                . $now->format('H:i')
                . '. Transport menunggu absen pulang.',
        ];
    }

    private function recordCheckOut(
        DailyAttendance $attendance,
        Teacher $teacher,
        Carbon $now,
        ?string $photoBase64
    ): array {
        if (! $attendance->check_in_time) {
            return [
                'status' => 'no_check_in',
                'type' => 'check_out',
                'name' => $teacher->name,
                'transport' => 0,
                'message' => $teacher->name
                    . ' belum melakukan absen masuk hari ini.',
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'status' => 'already',
                'type' => 'check_out',
                'name' => $teacher->name,
                'transport' => (int) $attendance->transport_amount,
                'message' => $teacher->name
                    . ' sudah absen pulang pukul '
                    . substr((string) $attendance->check_out_time, 0, 5)
                    . '.',
            ];
        }

        $setting = $this->activeSetting();

        $isValidCheckIn = $attendance->check_in_status === 'ontime';

        $isValidCheckOut = $setting
            ? $this->isWithinWindow(
                $now,
                (string) $setting->check_out_start,
                (string) $setting->check_out_end
            )
            : false;

        $transportAmount = (
            $setting
            && $isValidCheckIn
            && $isValidCheckOut
        )
            ? (int) $setting->amount
            : 0;

        $photoPath = $this->saveBase64Photo(
            $photoBase64,
            'attendance/check-out'
        );

        $attendance->update([
            'check_out_time' => $now->format('H:i:s'),
            'check_out_status' => $isValidCheckOut ? 'normal' : 'early',
            'check_out_photo' => $photoPath,
            'transport_amount' => $transportAmount,
            'note' => $setting
                ? (
                    $transportAmount > 0
                        ? 'Absen masuk dan pulang sesuai jadwal. Transport diberikan.'
                        : 'Transport tidak diberikan karena waktu masuk atau pulang tidak sesuai jadwal.'
                )
                : 'Absen pulang tercatat, tetapi pengaturan transport belum aktif.',
        ]);

        if ($transportAmount > 0) {
            AppNotification::create([
                'teacher_id' => $teacher->id,
                'title' => 'Transport diterima',
                'message' => 'Anda mendapatkan transport sebesar Rp '
                    . number_format($transportAmount, 0, ',', '.')
                    . ' hari ini.',
                'type' => 'success',
            ]);
        }

        return [
            'status' => 'success',
            'type' => 'check_out',
            'name' => $teacher->name,
            'check_out_status' => $isValidCheckOut ? 'normal' : 'early',
            'transport' => $transportAmount,
            'photo' => $photoPath,
            'message' => $teacher->name
                . ' berhasil absen pulang pukul '
                . $now->format('H:i')
                . '. Transport: Rp '
                . number_format($transportAmount, 0, ',', '.')
                . '.',
        ];
    }

    private function activeSetting(): ?TransportSetting
    {
        return TransportSetting::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    private function isWithinWindow(
        Carbon $now,
        string $start,
        string $end
    ): bool {
        $startTime = Carbon::parse(
            $now->toDateString() . ' ' . substr($start, 0, 8)
        );

        $endTime = Carbon::parse(
            $now->toDateString() . ' ' . substr($end, 0, 8)
        );

        if ($endTime->lt($startTime)) {
            $endTime->addDay();

            if ($now->lt($startTime)) {
                $now = $now->copy()->addDay();
            }
        }

        return $now->betweenIncluded($startTime, $endTime);
    }

    private function saveBase64Photo(
        ?string $photoBase64,
        string $folder
    ): ?string {
        if (! $photoBase64) {
            return null;
        }

        if (! preg_match(
            '/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/s',
            $photoBase64,
            $matches
        )) {
            return null;
        }

        $extension = match (strtolower($matches[1])) {
            'jpeg', 'jpg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            default => 'jpg',
        };

        $binary = base64_decode(
            str_replace(' ', '+', $matches[2]),
            true
        );

        if ($binary === false) {
            return null;
        }

        $fileName = $folder
            . '/'
            . uniqid('attendance_', true)
            . '.'
            . $extension;

        Storage::disk('public')->put($fileName, $binary);

        return $fileName;
    }
}
PHP_FILE_1

mkdir -p "app/Livewire/FaceAttendance"
cat > "app/Livewire/FaceAttendance/Index.php" <<'PHP_FILE_2'
<?php

namespace App\Livewire\FaceAttendance;

use App\Models\Teacher;
use App\Services\TeacherAttendanceService;
use Livewire\Component;

class Index extends Component
{
    public string $mode = 'check_in';

    public array $logs = [];

    public function mount(): void
    {
        $requestedMode = (string) request()->query(
            'mode',
            'check_in'
        );

        if (in_array(
            $requestedMode,
            ['check_in', 'check_out'],
            true
        )) {
            $this->mode = $requestedMode;
        }
    }

    public function setMode(string $mode): void
    {
        if (! in_array(
            $mode,
            ['check_in', 'check_out'],
            true
        )) {
            return;
        }

        $this->mode = $mode;
    }

    public function saveAttendanceByTeacherId(
        $teacherId,
        ?string $photoBase64 = null
    ): array {
        $teacher = Teacher::query()
            ->where('is_active', true)
            ->findOrFail($teacherId);

        $result = app(TeacherAttendanceService::class)
            ->record(
                $teacher,
                $this->mode,
                $photoBase64
            );

        if (($result['status'] ?? null) === 'success') {
            $this->addLog(
                (string) $result['name'],
                $this->mode === 'check_in'
                    ? 'Masuk'
                    : 'Pulang',
                now()->format('H:i:s'),
                (int) ($result['transport'] ?? 0)
            );
        }

        return $result;
    }

    private function addLog(
        string $name,
        string $type,
        string $time,
        int $transport
    ): void {
        $newLog = [
            'name' => $name,
            'type' => $type,
            'time' => $time,
            'transport' => $transport,
        ];

        $this->logs = array_slice(
            array_merge([$newLog], $this->logs),
            0,
            10
        );
    }

    public function render()
    {
        return view('livewire.face-attendance.index', [
            'teachers' => Teacher::query()
                ->where('is_active', true)
                ->whereNotNull('face_descriptor')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}
PHP_FILE_2

mkdir -p "app/Livewire/Kiosk"
cat > "app/Livewire/Kiosk/Index.php" <<'PHP_FILE_3'
<?php

namespace App\Livewire\Kiosk;

use App\Models\Teacher;
use App\Services\TeacherAttendanceService;
use App\Services\WhatsappService;
use Livewire\Component;
use Throwable;

class Index extends Component
{
    public string $mode = 'check_in';

    public function mount(): void
    {
        $requestedMode = (string) request()->query(
            'mode',
            'check_in'
        );

        if (in_array(
            $requestedMode,
            ['check_in', 'check_out'],
            true
        )) {
            $this->mode = $requestedMode;
        }
    }

    public function setMode(string $mode): void
    {
        if (in_array(
            $mode,
            ['check_in', 'check_out'],
            true
        )) {
            $this->mode = $mode;
        }
    }

    public function saveAttendanceByTeacherId(
        $teacherId,
        ?string $photoBase64 = null
    ): array {
        $teacher = Teacher::query()
            ->where('is_active', true)
            ->findOrFail($teacherId);

        $result = app(TeacherAttendanceService::class)
            ->record(
                $teacher,
                $this->mode,
                $photoBase64
            );

        $result['wa_sent'] = false;

        if (
            ($result['status'] ?? null) === 'success'
            && filled($teacher->phone)
        ) {
            try {
                $response = WhatsappService::send(
                    $teacher->phone,
                    $this->buildWhatsappMessage(
                        $teacher,
                        $result
                    )
                );

                $result['wa_sent'] = method_exists(
                    $response,
                    'successful'
                )
                    ? $response->successful()
                    : true;
            } catch (Throwable $exception) {
                report($exception);
                $result['wa_sent'] = false;
            }
        }

        return $result;
    }

    private function buildWhatsappMessage(
        Teacher $teacher,
        array $result
    ): string {
        $type = ($result['type'] ?? '') === 'check_out'
            ? 'Pulang'
            : 'Masuk';

        $status = match (
            $result['check_in_status']
                ?? $result['check_out_status']
                ?? null
        ) {
            'ontime' => 'Tepat Waktu',
            'late' => 'Terlambat',
            'normal' => 'Sesuai Jadwal',
            'early' => 'Di Luar Jadwal',
            default => '-',
        };

        return "Assalamu'alaikum Wr. Wb.\n\n"
            . "Absensi guru berhasil dicatat.\n\n"
            . "Nama: {$teacher->name}\n"
            . "Tanggal: " . now()->format('d-m-Y') . "\n"
            . "Jenis: Absen {$type}\n"
            . "Waktu: " . now()->format('H:i') . "\n"
            . "Status: {$status}\n"
            . "Transport: Rp "
            . number_format(
                (int) ($result['transport'] ?? 0),
                0,
                ',',
                '.'
            )
            . "\n\n"
            . "Sistem Absensi Assaidiyyah";
    }

    public function render()
    {
        return view('livewire.kiosk.index', [
            'teachers' => Teacher::query()
                ->where('is_active', true)
                ->whereNotNull('face_descriptor')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
PHP_FILE_3

mkdir -p "app/Livewire/DailyAttendances"
cat > "app/Livewire/DailyAttendances/Index.php" <<'PHP_FILE_4'
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
PHP_FILE_4

mkdir -p "app/Livewire/SubjectAttendances"
cat > "app/Livewire/SubjectAttendances/Index.php" <<'PHP_FILE_5'
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
PHP_FILE_5

mkdir -p "app/Livewire/PicketSubjectAttendances"
cat > "app/Livewire/PicketSubjectAttendances/Index.php" <<'PHP_FILE_6'
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
PHP_FILE_6

mkdir -p "app/Livewire/TeachingSchedules"
cat > "app/Livewire/TeachingSchedules/Index.php" <<'PHP_FILE_7'
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
PHP_FILE_7

echo "Memeriksa sintaks PHP..."
php -l "app/Services/TeacherAttendanceService.php"
php -l "app/Livewire/FaceAttendance/Index.php"
php -l "app/Livewire/Kiosk/Index.php"
php -l "app/Livewire/DailyAttendances/Index.php"
php -l "app/Livewire/SubjectAttendances/Index.php"
php -l "app/Livewire/PicketSubjectAttendances/Index.php"
php -l "app/Livewire/TeachingSchedules/Index.php"

echo "Membersihkan cache Laravel..."
php artisan optimize:clear

echo ""
echo "PERBAIKAN SELESAI."
echo "Backup file lama tersimpan di: $BACKUP_DIR"
echo "Silakan uji absen masuk, absen pulang, absensi mapel, jadwal, dan guru piket."

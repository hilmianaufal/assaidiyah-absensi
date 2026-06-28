<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AppNotification;
use App\Models\DailyAttendance;
use App\Models\DhuhaReport;
use App\Models\DhuhaSchedule;
use App\Models\MonthlyHonor;
use App\Models\PicketReport;
use App\Models\PicketReportStudent;
use App\Models\SubjectAttendance;
use App\Models\Teacher;
use App\Models\TeacherHonorPackage;
use App\Models\TeacherPicketSchedule;
use App\Models\TeachingSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TeacherAppController extends Controller
{
    public function dashboard(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return response()->json([
                'message' => 'Data guru tidak ditemukan.'
            ], 404);
        }

        $todayAttendance = DailyAttendance::where(
            'teacher_id',
            $teacher->id
        )
        ->whereDate('attendance_date', now())
        ->first();

        $monthHonor = MonthlyHonor::where(
            'teacher_id',
            $teacher->id
        )
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('grand_total');

        $today = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Ahad',
        ][now()->format('l')];

        $todaySchedules = TeachingSchedule::with([
            'subject',
            'institution'
        ])
        ->where('teacher_id', $teacher->id)
        ->where('day', $today)
        ->orderBy('start_time')
        ->get();

        $todayPicket = TeacherPicketSchedule::with('institution')
            ->where('teacher_id', $teacher->id)
            ->where('day', $today)
            ->where('is_active', true)
            ->first();

        return response()->json([
            'teacher' => $teacher,
            'attendance_today' => $todayAttendance,
            'month_honor' => $monthHonor,
            'today_schedules' => $todaySchedules,
            'today_picket' => $todayPicket,
        ]);
    }


    public function dhuha(Request $request)
{
    $teacher = $request->user()->teacher;

    $day = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Ahad',
    ][now()->format('l')];

    $schedule = DhuhaSchedule::where('teacher_id', $teacher->id)
        ->where('day', $day)
        ->where('is_active', true)
        ->first();

    $report = DhuhaReport::where('teacher_id', $teacher->id)
        ->whereDate('report_date', now())
        ->first();

    return response()->json([
        'is_allowed' => (bool) $schedule,
        'schedule' => $schedule,
        'report' => $report,
        'teachers' => Teacher::where('is_active', true)->orderBy('name')->get(),
    ]);
}

public function saveDhuha(Request $request)
{
    $teacher = $request->user()->teacher;

    $data = $request->validate([
        'status' => ['required', 'in:done,not_done'],
        'present_teacher_ids' => ['nullable', 'array'],
        'present_teacher_ids.*' => ['exists:teachers,id'],
        'note' => ['nullable', 'string'],
    ]);

    $presentIds = $data['present_teacher_ids'] ?? [];

    $presentTeachers = Teacher::whereIn('id', $presentIds)->orderBy('name')->get();

    $teacherList = $presentTeachers->isNotEmpty()
        ? $presentTeachers->values()->map(fn ($item, $index) => ($index + 1) . '. ' . $item->name)->implode("\n")
        : '-';

    $statusText = $data['status'] === 'done' ? 'Terlaksana' : 'Tidak Terlaksana';

    $message = "📢 *LAPORAN SHOLAT DHUHA*\n\n"
        . "Hari/Tanggal : " . now()->locale('id')->translatedFormat('l, d F Y') . "\n"
        . "Pelapor      : {$teacher->name}\n"
        . "Status       : {$statusText}\n\n"
        . "*Guru Hadir:*\n{$teacherList}\n\n"
        . "Jumlah Hadir : " . $presentTeachers->count() . " guru\n\n"
        . "Keterangan:\n"
        . ($data['note'] ?? '-') . "\n\n"
        . "Wassalamu’alaikum Wr. Wb.";

    $report = DhuhaReport::updateOrCreate(
        [
            'teacher_id' => $teacher->id,
            'report_date' => now()->toDateString(),
        ],
        [
            'institution_id' => null,
            'status' => $data['status'],
            'present_teacher_ids' => $presentIds,
            'teacher_count' => count($presentIds),
            'imam_name' => null,
            'note' => $data['note'] ?? null,
            'whatsapp_message' => $message,
        ]
    );

    return response()->json([
        'message' => 'Laporan dhuha berhasil disimpan.',
        'report' => $report,
    ]);
}

public function picketSubjectAttendances(Request $request)
{
    $teacher = $request->user()->teacher;

    $day = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Ahad',
    ][now()->format('l')];

    $picketSchedule = TeacherPicketSchedule::with('institution')
        ->where('teacher_id', $teacher->id)
        ->where('day', $day)
        ->where('is_active', true)
        ->first();

    if (! $teacher->is_picket_officer || ! $picketSchedule) {
        return response()->json([
            'is_allowed' => false,
            'message' => 'Anda tidak memiliki jadwal piket hari ini.',
            'schedules' => [],
            'attendances' => [],
            'picket_schedule' => null,
        ]);
    }

    $schedules = TeachingSchedule::with(['teacher', 'subject', 'institution'])
        ->where('day', $day)
        ->where('institution_id', $picketSchedule->institution_id)
        ->orderBy('start_time')
        ->get();

    $attendances = SubjectAttendance::whereDate('teaching_date', now()->toDateString())
        ->where('institution_id', $picketSchedule->institution_id)
        ->get()
        ->keyBy('teaching_schedule_id');

    return response()->json([
        'is_allowed' => true,
        'picket_schedule' => $picketSchedule,
        'schedules' => $schedules,
        'attendances' => $attendances,
    ]);
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

public function picketReport(Request $request)
{
    $teacher = $request->user()->teacher;

    $report = PicketReport::with('students')
        ->where('teacher_id', $teacher->id)
        ->whereDate('report_date', now()->toDateString())
        ->first();

    return response()->json([
        'report' => $report,
        'classes' => [
            'Kelas 10',
            'Kelas 11 Paket 1',
            'Kelas 11 Paket 2',
            'Kelas 12 Paket 1',
            'Kelas 12 Paket 2',
        ],
    ]);
}

public function savePicketReport(Request $request)
{
    $teacher = $request->user()->teacher;

    $data = $request->validate([
        'teacher_absences' => ['nullable', 'string'],
        'students' => ['nullable', 'array'],
        'students.*.class_name' => ['required', 'string'],
        'students.*.student_name' => ['required', 'string'],
        'students.*.status' => ['required', 'string'],
    ]);

    $classes = [
        'Kelas 10',
        'Kelas 11 Paket 1',
        'Kelas 11 Paket 2',
        'Kelas 12 Paket 1',
        'Kelas 12 Paket 2',
    ];

    $teacherAbsences = trim($data['teacher_absences'] ?? '') ?: '-';

    $message = "Bismillahirrahmanirrahim...\n\n";
    $message .= "📅 " . now()->locale('id')->translatedFormat('l, d F Y') . "\n";
    $message .= "📌 Guru Piket: {$teacher->name}\n\n";
    $message .= "🔖 Guru yang berhalangan hadir :\n";
    $message .= "{$teacherAbsences}\n\n";
    $message .= "📝 Siswa yang tidak masuk :\n\n";

    foreach ($classes as $class) {
        $message .= "🏘️ {$class}\n";

        $items = collect($data['students'] ?? [])
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

    $report = PicketReport::updateOrCreate(
        [
            'teacher_id' => $teacher->id,
            'report_date' => now()->toDateString(),
        ],
        [
            'teacher_absences' => $teacherAbsences,
            'whatsapp_message' => $message,
        ]
    );

    $report->students()->delete();

    foreach ($data['students'] ?? [] as $student) {
        PicketReportStudent::create([
            'picket_report_id' => $report->id,
            'class_name' => $student['class_name'],
            'student_name' => $student['student_name'],
            'status' => $student['status'],
        ]);
    }

    return response()->json([
        'message' => 'Laporan piket berhasil disimpan.',
        'report' => $report->load('students'),
    ]);
}

public function honors(Request $request)
{
    $teacher = $request->user()->teacher;

    $month = $request->query('month', now()->month);
    $year = $request->query('year', now()->year);

    $honors = MonthlyHonor::with(['institution', 'payments'])
        ->where('teacher_id', $teacher->id)
        ->where('month', $month)
        ->where('year', $year)
        ->get();

    $subjectAttendances = \App\Models\SubjectAttendance::with(['subject', 'institution'])
        ->where('teacher_id', $teacher->id)
        ->whereMonth('teaching_date', $month)
        ->whereYear('teaching_date', $year)
        ->latest('teaching_date')
        ->get();

    $runningTeachingHonor = $subjectAttendances
        ->whereIn('attendance_status', ['present', 'late'])
        ->sum('teaching_honor');

    $runningHours = $subjectAttendances
        ->whereIn('attendance_status', ['present', 'late'])
        ->sum('hours_count');

    $additionalHonors = \App\Models\AdditionalHonor::with('institution')
        ->where('teacher_id', $teacher->id)
        ->where('month', $month)
        ->where('year', $year)
        ->latest()
        ->get();

    $runningTransport = \App\Models\DailyAttendance::where('teacher_id', $teacher->id)
        ->whereMonth('attendance_date', $month)
        ->whereYear('attendance_date', $year)
        ->sum('transport_amount');

    return response()->json([
        'honors' => $honors,
        'total_honor' => $honors->sum('grand_total'),

        'running' => [
            'teaching_honor' => $runningTeachingHonor,
            'hours' => $runningHours,
            'transport' => $runningTransport,
            'additional_honor' => $additionalHonors->sum('amount'),
            'estimated_total' =>
                $runningTeachingHonor +
                $runningTransport +
                $additionalHonors->sum('amount'),
    ],

        'subject_attendances' => $subjectAttendances,
        'additional_honors' => $additionalHonors,
    ]);
}

public function honorSlip(Request $request, MonthlyHonor $honor)
{
    $teacher = $request->user()->teacher;

    abort_if($honor->teacher_id !== $teacher->id, 403);

    $honor->load([
        'teacher',
        'institution',
        'payments',
    ]);

    $pdf = Pdf::loadView(
        'pdf.teacher-honor-slip',
        compact('honor')
    )->setPaper('a4');

    return $pdf->download(
        'slip-honor-'.$honor->month.'-'.$honor->year.'.pdf'
    );
}

public function attendances(Request $request)
{
    $teacher = $request->user()->teacher;

    $month = $request->query('month', now()->month);
    $year = $request->query('year', now()->year);

    $attendances = DailyAttendance::where('teacher_id', $teacher->id)
        ->whereMonth('attendance_date', $month)
        ->whereYear('attendance_date', $year)
        ->latest('attendance_date')
        ->get();

    return response()->json([
        'month' => (int) $month,
        'year' => (int) $year,
        'attendances' => $attendances,
    ]);
}

public function announcements(Request $request)
{
    $announcements = Announcement::where('is_active', true)
        ->latest()
        ->take(30)
        ->get();

    return response()->json([
        'announcements' => $announcements,
    ]);
}

public function updateProfile(Request $request)
{
    $user = $request->user();
    $teacher = $user->teacher;

    if (! $teacher) {
        return response()->json([
            'message' => 'Data guru tidak ditemukan.',
        ], 404);
    }

    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users,email,' . $user->id],
        'phone' => ['nullable', 'string', 'max:50'],
        'password' => ['nullable', 'string', 'min:6'],
        'photo' => ['nullable', 'image', 'max:2048'],
    ]);

    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $file->storeAs('uploads/teachers', $fileName, 'public');

        $teacher->photo = 'storage/uploads/teachers/' . $fileName;
    }

    $teacher->update([
        'name' => $data['name'],
        'phone' => $data['phone'] ?? null,
        'photo' => $teacher->photo,
    ]);

    $userData = [
        'name' => $data['name'],
        'email' => $data['email'],
    ];

    if (! empty($data['password'])) {
        $userData['password'] = Hash::make($data['password']);
    }

    $user->update($userData);

    return response()->json([
        'message' => 'Profil berhasil diperbarui.',
        'user' => $user->load('teacher'),
    ]);
}

public function notifications(Request $request)
{
    $teacher = $request->user()->teacher;

    $notifications = AppNotification::where(function ($query) use ($teacher) {
            $query->whereNull('teacher_id')
                ->orWhere('teacher_id', $teacher->id);
        })
        ->latest()
        ->take(50)
        ->get();

    $unreadCount = $notifications->whereNull('read_at')->count();

    return response()->json([
        'unread_count' => $unreadCount,
        'notifications' => $notifications,
    ]);
}

public function markNotificationAsRead(Request $request, AppNotification $notification)
{
    $teacher = $request->user()->teacher;

    if ($notification->teacher_id && $notification->teacher_id !== $teacher->id) {
        abort(403);
    }

    $notification->update([
        'read_at' => now(),
    ]);

    return response()->json([
        'message' => 'Notifikasi sudah dibaca.',
        'notification' => $notification,
    ]);
}

}

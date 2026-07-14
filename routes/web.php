<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DailyAttendancePdfController;
use App\Http\Controllers\HonorPaymentReceiptController;
use App\Http\Controllers\HonorPdfController;
use App\Http\Controllers\InstitutionHonorReportPdfController;
use App\Http\Controllers\MobileAdminSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubjectAttendancePdfController;
use App\Http\Controllers\TeacherHonorPdfController;

use App\Livewire\AdditionalHonors\Index as AdditionalHonorsIndex;
use App\Livewire\DailyAttendances\Index as DailyAttendancesIndex;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\DhuhaReports\Index as DhuhaReportsIndex;
use App\Livewire\DhuhaSchedules\Index as DhuhaSchedulesIndex;
use App\Livewire\FaceAttendance\Index as FaceAttendanceIndex;
use App\Livewire\FaceEnrollment\Index as FaceEnrollmentIndex;
use App\Livewire\FinanceDashboard\Index as FinanceDashboardIndex;
use App\Livewire\Kiosk\Index as KioskIndex;
use App\Livewire\MonthlyHonors\Index as MonthlyHonorsIndex;
use App\Livewire\PicketReports\Create as PicketReportCreate;
use App\Livewire\PicketSchedules\Index as PicketSchedulesIndex;
use App\Livewire\PicketSubjectAttendances\Index as PicketSubjectAttendancesIndex;
use App\Livewire\SubjectAttendances\Index as SubjectAttendancesIndex;
use App\Livewire\Subjects\Index as SubjectsIndex;
use App\Livewire\TeacherHonorPackages\Index as TeacherHonorPackagesIndex;
use App\Livewire\TeacherPortal\Attendances as TeacherAttendances;
use App\Livewire\TeacherPortal\Dashboard as TeacherDashboard;
use App\Livewire\TeacherPortal\DhuhaReport as TeacherDhuhaReport;
use App\Livewire\TeacherPortal\Honors as TeacherHonors;
use App\Livewire\TeacherPortal\Profile as TeacherProfile;
use App\Livewire\TeacherPortal\Schedules as TeacherSchedules;
use App\Livewire\Teachers\Index as TeachersIndex;
use App\Livewire\TeachingSchedules\Index as TeachingSchedulesIndex;
use App\Livewire\TransportSettings\Index as TransportSettingsIndex;
use App\Livewire\Users\Index as UsersIndex;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Awal
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

/*
|--------------------------------------------------------------------------
| Jembatan Login Admin Android
|--------------------------------------------------------------------------
|
| Route ini harus berada di luar middleware auth karena route inilah
| yang menerima token sekali pakai dan membuat sesi login web Laravel.
|
*/

Route::get(
    '/mobile/admin/session/{token}',
    MobileAdminSessionController::class
)->name('mobile.admin.session');

/*
|--------------------------------------------------------------------------
| Route Yang Membutuhkan Login
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard dan Profil Admin
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', DashboardIndex::class)
        ->name('dashboard');

    Route::get('/finance-dashboard', FinanceDashboardIndex::class)
        ->name('finance-dashboard.index');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Absensi Wajah Admin
    |--------------------------------------------------------------------------
    */

    Route::get('/face-attendance', FaceAttendanceIndex::class)
        ->middleware('can:admin-only')
        ->name('face-attendance.index');

    Route::get('/face-enrollment', FaceEnrollmentIndex::class)
        ->middleware('can:admin-only')
        ->name('face-enrollment.index');

    Route::get('/kiosk', KioskIndex::class)
        ->middleware('can:admin-only')
        ->name('kiosk.index');

    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */

    Route::get('/teachers', TeachersIndex::class)
        ->name('teachers.index');

    Route::get('/subjects', SubjectsIndex::class)
        ->name('subjects.index');

    Route::get('/users', UsersIndex::class)
        ->name('users.index');

    Route::get('/teaching-schedules', TeachingSchedulesIndex::class)
        ->name('teaching-schedules.index');

    Route::get('/picket-schedules', PicketSchedulesIndex::class)
        ->name('picket-schedules.index');

    Route::get('/teacher-honor-packages', TeacherHonorPackagesIndex::class)
        ->name('teacher-honor-packages.index');

    Route::get('/transport-settings', TransportSettingsIndex::class)
        ->name('transport-settings.index');

    /*
    |--------------------------------------------------------------------------
    | Absensi
    |--------------------------------------------------------------------------
    */

    Route::get('/daily-attendances', DailyAttendancesIndex::class)
        ->name('daily-attendances.index');

    Route::get('/subject-attendances', SubjectAttendancesIndex::class)
        ->name('subject-attendances.index');

    Route::get(
        '/picket-subject-attendances',
        PicketSubjectAttendancesIndex::class
    )->name('picket-subject-attendances.index');

    Route::get('/picket-reports/create', PicketReportCreate::class)
        ->name('picket-reports.create');

    /*
    |--------------------------------------------------------------------------
    | Honor
    |--------------------------------------------------------------------------
    */

    Route::get('/monthly-honors', MonthlyHonorsIndex::class)
        ->name('monthly-honors.index');

    Route::get('/additional-honors', AdditionalHonorsIndex::class)
        ->name('additional-honors.index');

    /*
    |--------------------------------------------------------------------------
    | Portal Guru
    |--------------------------------------------------------------------------
    */

    Route::get('/teacher/dashboard', TeacherDashboard::class)
        ->name('teacher.dashboard');

    Route::get('/teacher/attendances', TeacherAttendances::class)
        ->name('teacher.attendances');

    Route::get('/teacher/schedules', TeacherSchedules::class)
        ->name('teacher.schedules');

    Route::get('/teacher/honors', TeacherHonors::class)
        ->name('teacher.honors');

    Route::get('/teacher/profile', TeacherProfile::class)
        ->name('teacher.profile');

    Route::get('/teacher/dhuha-report', TeacherDhuhaReport::class)
        ->name('teacher.dhuha-report');

    /*
    |--------------------------------------------------------------------------
    | Dhuha
    |--------------------------------------------------------------------------
    */

    Route::get('/dhuha-schedules', DhuhaSchedulesIndex::class)
        ->name('dhuha-schedules.index');

    Route::get('/dhuha-reports', DhuhaReportsIndex::class)
        ->name('dhuha-reports.index');

    /*
    |--------------------------------------------------------------------------
    | Laporan PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/honor-reports/institution/pdf/{institution}/{month}/{year}',
        [InstitutionHonorReportPdfController::class, 'show']
    )->name('honor-reports.institution.pdf');

    Route::get(
        '/monthly-honors/{honor}/pdf',
        [HonorPdfController::class, 'show']
    )->name('monthly-honors.pdf');

    Route::get(
        '/teacher/honors/{honor}/pdf',
        [TeacherHonorPdfController::class, 'downloadByHonor']
    )->name('teacher.honors.pdf-by-honor');

    Route::get(
        '/teacher/honors/pdf/{month}/{year}',
        [TeacherHonorPdfController::class, 'download']
    )->name('teacher.honors.pdf');

    Route::get(
        '/subject-attendances/pdf/{date}',
        [SubjectAttendancePdfController::class, 'show']
    )->name('subject-attendances.pdf');

    Route::get(
        '/daily-attendances/pdf/{date}',
        [DailyAttendancePdfController::class, 'show']
    )->name('daily-attendances.pdf');

    Route::get(
        '/honor-payments/{payment}/receipt',
        [HonorPaymentReceiptController::class, 'show']
    )->name('honor-payments.receipt');

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [AuthenticatedSessionController::class, 'destroy']
    )->name('logout');
});

require __DIR__ . '/auth.php';

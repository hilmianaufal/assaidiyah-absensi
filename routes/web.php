<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DailyAttendancePdfController;
use App\Http\Controllers\HonorPdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubjectAttendancePdfController;
use App\Http\Controllers\TeacherHonorPdfController;
use App\Livewire\DailyAttendances\Index as DailyAttendancesIndex;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\FaceAttendance\Index as FaceAttendanceIndex;
use App\Livewire\FaceEnrollment\Index as FaceEnrollmentIndex;
use App\Livewire\Kiosk\Index as KioskIndex;
use App\Livewire\MonthlyHonors\Index as MonthlyHonorsIndex;
use App\Livewire\PicketReports\Create as PicketReportCreate;
use App\Livewire\PicketSchedules\Index as PicketSchedulesIndex;
use App\Livewire\PicketSubjectAttendances\Index as PicketSubjectAttendancesIndex;
use App\Livewire\SubjectAttendances\Index as SubjectAttendancesIndex;
use App\Livewire\Subjects\Index as SubjectsIndex;
use App\Livewire\TeacherPortal\Attendances as TeacherAttendances;
use App\Livewire\TeacherPortal\Dashboard as TeacherDashboard;
use App\Livewire\TeacherPortal\Honors as TeacherHonors;
use App\Livewire\TeacherPortal\Schedules as TeacherSchedules;
use App\Livewire\Teachers\Index as TeachersIndex;
use App\Livewire\TeachingSchedules\Index as TeachingSchedulesIndex;
use App\Livewire\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use App\Livewire\AdditionalHonors\Index as AdditionalHonorsIndex;







Route::get('/teacher/honors/pdf/{month}/{year}', [TeacherHonorPdfController::class, 'download'])
    ->middleware(['auth'])
    ->name('teacher.honors.pdf');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardIndex::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');  //  Route::get('/teachers', TeachersIndex::class)->name('teachers.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/teachers', TeachersIndex::class)->name('teachers.index');
    Route::get('/subjects', SubjectsIndex::class)->name('subjects.index');
    Route::get('/daily-attendances', DailyAttendancesIndex::class)
    ->name('daily-attendances.index');
    Route::get('/teaching-schedules', TeachingSchedulesIndex::class)->name('teaching-schedules.index');

    Route::get('/subject-attendances', SubjectAttendancesIndex::class)
        ->name('subject-attendances.index');


Route::middleware(['auth'])->group(function () {
    Route::get('/teachers', TeachersIndex::class)->name('teachers.index');

    Route::get('/daily-attendances', DailyAttendancesIndex::class)
        ->name('daily-attendances.index');
        Route::get('/face-attendance', FaceAttendanceIndex::class)
            ->name('face-attendance.index');

            Route::get('/monthly-honors', MonthlyHonorsIndex::class)
                ->name('monthly-honors.index');


Route::get('/monthly-honors/{honor}/pdf', [HonorPdfController::class, 'show'])
    ->middleware(['auth'])
    ->name('monthly-honors.pdf');



Schedule::command('honor:generate-monthly')
    ->monthlyOn(1, '00:10')
    ->withoutOverlapping();

Route::get('/face-enrollment', FaceEnrollmentIndex::class)
    ->name('face-enrollment.index');



Route::get('/picket-schedules', PicketSchedulesIndex::class)
    ->middleware(['auth'])
    ->name('picket-schedules.index');


Route::get('/picket-reports/create', PicketReportCreate::class)
    ->middleware(['auth'])
    ->name('picket-reports.create');


Route::get('/teacher/dashboard', TeacherDashboard::class)
    ->middleware(['auth'])
    ->name('teacher.dashboard');



Route::get('/teacher/attendances', TeacherAttendances::class)
    ->middleware(['auth'])
    ->name('teacher.attendances');



Route::get('/additional-honors', AdditionalHonorsIndex::class)
    ->middleware(['auth'])
    ->name('additional-honors.index');

Route::get('/users', UsersIndex::class)
    ->middleware(['auth'])
    ->name('users.index');


Route::get('/teacher/schedules', TeacherSchedules::class)
    ->middleware(['auth'])
    ->name('teacher.schedules');

Route::get('/teacher/honors', TeacherHonors::class)
    ->middleware(['auth'])
    ->name('teacher.honors');


Route::get('/teacher/honors/pdf/{month}/{year}', [TeacherHonorPdfController::class, 'download'])
    ->middleware(['auth'])
    ->name('teacher.honors.pdf');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

});



Route::get('/kiosk', KioskIndex::class)
    ->middleware(['auth'])
    ->name('kiosk.index');

Route::get('/subject-attendances/pdf/{date}', [SubjectAttendancePdfController::class, 'show'])
    ->middleware(['auth'])
    ->name('subject-attendances.pdf');

Route::get('/daily-attendances/pdf/{date}', [DailyAttendancePdfController::class, 'show'])
    ->middleware(['auth'])
    ->name('daily-attendances.pdf');

Route::get('/picket-subject-attendances', PicketSubjectAttendancesIndex::class)
    ->middleware(['auth'])
    ->name('picket-subject-attendances.index');



    });

require __DIR__.'/auth.php';

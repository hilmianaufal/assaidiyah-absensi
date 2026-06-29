<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeacherAppController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/teacher/dashboard', [TeacherAppController::class, 'dashboard']);
    Route::get('/teacher/attendances', [TeacherAppController::class, 'attendances']);
    Route::get('/teacher/schedules', [TeacherAppController::class, 'schedules']);
    Route::get('/teacher/honors', [TeacherAppController::class, 'honors']);
    Route::get('/teacher/honors/{honor}/slip', [TeacherAppController::class, 'honorSlip']);

    Route::get('/teacher/dhuha', [TeacherAppController::class, 'dhuha']);
    Route::post('/teacher/dhuha', [TeacherAppController::class, 'saveDhuha']);

    Route::get('/teacher/picket-subject-attendances', [TeacherAppController::class, 'picketSubjectAttendances']);
    Route::post('/teacher/picket-subject-attendances', [TeacherAppController::class, 'markPicketSubjectAttendance']);

    Route::get('/teacher/picket-report', [TeacherAppController::class, 'picketReport']);
    Route::post('/teacher/picket-report', [TeacherAppController::class, 'savePicketReport']);

    Route::get('/teacher/announcements', [TeacherAppController::class, 'announcements']);

    Route::get('/teacher/notifications', [TeacherAppController::class, 'notifications']);
    Route::post('/teacher/notifications/{notification}/read', [TeacherAppController::class, 'markNotificationAsRead']);

    Route::post('/teacher/profile', [TeacherAppController::class, 'updateProfile']);
});

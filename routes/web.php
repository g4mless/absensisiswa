<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\AdminTeacherController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherSubjectController;
use App\Http\Controllers\ProgramHeadController;
use App\Http\Controllers\PklSupervisorController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SchoolLocationController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPklController;
use App\Http\Controllers\DutyTeacherController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('students', AdminStudentController::class);
    Route::resource('teachers', AdminTeacherController::class);
    Route::resource('classes', ClassController::class);
    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('teacher-subjects', TeacherSubjectController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('program-heads', ProgramHeadController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('pkl-supervisors', PklSupervisorController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('schedules', ScheduleController::class);

    Route::get('/school-location', [SchoolLocationController::class, 'index'])->name('school-location.index');
    Route::put('/school-location', [SchoolLocationController::class, 'update'])->name('school-location.update');

    Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');

    Route::get('/profile', fn() => view('admin.profile'))->name('profile');
});

Route::prefix('teacher')->name('teacher.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('/schedule', [TeacherController::class, 'schedule'])->name('schedule');
    Route::get('/classes', [TeacherController::class, 'classes'])->name('classes');
    Route::get('/classes/{id}', [TeacherController::class, 'showClass'])->name('classes.show');
    Route::get('/attendance', [TeacherController::class, 'attendance'])->name('attendance');
    Route::get('/attendance/{sessionId}', [TeacherController::class, 'showSession'])->name('attendance.show');
    Route::post('/attendance/{sessionId}', [TeacherController::class, 'updateSession'])->name('attendance.update');
    Route::get('/students/{id}', [TeacherController::class, 'showStudent'])->name('students.show');
    Route::get('/excuses', [TeacherController::class, 'excuses'])->name('excuses');
    Route::get('/excuses/{id}', [TeacherController::class, 'showExcuse'])->name('excuses.show');
    Route::post('/excuses/{id}/approve', [TeacherController::class, 'approveExcuse'])->name('excuses.approve');
    Route::post('/excuses/{id}/reject', [TeacherController::class, 'rejectExcuse'])->name('excuses.reject');
    Route::get('/reports', [TeacherController::class, 'reports'])->name('reports');
    Route::get('/profile', fn() => view('teacher.profile'))->name('profile');
});

Route::prefix('student')->name('student.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [StudentController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/checkin', [StudentController::class, 'checkin'])->name('attendance.checkin');
    Route::get('/history', [StudentController::class, 'history'])->name('history');
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
    Route::put('/profile', [StudentController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [StudentController::class, 'updatePassword'])->name('profile.password');
});

Route::prefix('student-pkl')->name('student-pkl.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [StudentPklController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [StudentPklController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/checkin', [StudentPklController::class, 'checkin'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [StudentPklController::class, 'checkout'])->name('attendance.checkout');
    Route::get('/location', [StudentPklController::class, 'location'])->name('location');
    Route::post('/location/send', [StudentPklController::class, 'sendLocation'])->name('location.send');
    Route::get('/history', [StudentPklController::class, 'history'])->name('history');
    Route::get('/profile', [StudentPklController::class, 'profile'])->name('profile');
    Route::put('/profile', [StudentPklController::class, 'updateProfile'])->name('profile.update');
});

Route::prefix('duty-teacher')->name('duty-teacher.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DutyTeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance/today', [DutyTeacherController::class, 'today'])->name('attendance.today');
    Route::get('/attendance/all', [DutyTeacherController::class, 'all'])->name('attendance.all');
    Route::get('/reports/semester', [DutyTeacherController::class, 'semester'])->name('reports.semester');
});

Route::prefix('pkl-supervisor')->name('pkl-supervisor.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [PklSupervisorController::class, 'supervisorDashboard'])->name('dashboard');
    Route::get('/students', [PklSupervisorController::class, 'students'])->name('students');
    Route::get('/students/{id}', [PklSupervisorController::class, 'showStudent'])->name('students.show');
    Route::get('/attendance', [PklSupervisorController::class, 'attendance'])->name('attendance');
    Route::get('/locations', [PklSupervisorController::class, 'locations'])->name('locations');
    Route::get('/locations/{studentId}', [PklSupervisorController::class, 'showLocation'])->name('locations.show');
});

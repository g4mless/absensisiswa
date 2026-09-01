<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\DailyAttendance;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $activeClasses = ClassModel::count();
        $todayAttendance = DailyAttendance::whereDate('date', today())->count();
        $recentAttendance = DailyAttendance::with(['student.user', 'student.class'])
            ->orderByDesc('date')
            ->orderByDesc('check_in_time')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalTeachers',
            'activeClasses',
            'todayAttendance',
            'recentAttendance'
        ));
    }
}

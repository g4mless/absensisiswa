<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\Attendance;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $activeClasses = ClassModel::count();
        $todayAttendance = Attendance::whereDate('created_at', today())->count();
        $recentAttendance = Attendance::with(['student.user', 'student.class'])
            ->orderByDesc('created_at')
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

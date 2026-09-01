<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DutyTeacherController extends Controller
{
    public function dashboard()
    {
        $attendanceIssues = collect();
        $summary = collect();
        $recentNotices = collect();
        return view('duty-teacher.dashboard', compact('attendanceIssues', 'summary', 'recentNotices'));
    }

    public function today()
    {
        $todayAttendance = collect();
        return view('duty-teacher.attendance.today', compact('todayAttendance'));
    }

    public function all()
    {
        $attendanceData = collect();
        return view('duty-teacher.attendance.all', compact('attendanceData'));
    }

    public function semester()
    {
        $semesterData = collect();
        $problemStudents = collect();
        return view('duty-teacher.reports.semester', compact('semesterData', 'problemStudents'));
    }
}

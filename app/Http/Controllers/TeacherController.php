<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $todaySchedule = collect();
        $recentExcuses = collect();
        return view('teacher.dashboard', compact('todaySchedule', 'recentExcuses'));
    }

    public function schedule()
    {
        $timeSlots = collect();
        return view('teacher.schedule', compact('timeSlots'));
    }

    public function classes()
    {
        $classes = collect();
        return view('teacher.classes.index', compact('classes'));
    }

    public function showClass($id)
    {
        $students = collect();
        return view('teacher.classes.show', compact('students'));
    }

    public function attendance()
    {
        $sessions = collect();
        $selectedSessionData = null;
        return view('teacher.attendance.index', compact('sessions', 'selectedSessionData'));
    }

    public function showSession($sessionId)
    {
        $session = null;
        return view('teacher.attendance.session', compact('session'));
    }

    public function updateSession(Request $request, $sessionId)
    {
        return redirect()->route('teacher.attendance.show', $sessionId);
    }

    public function showStudent($id)
    {
        $student = null;
        return view('teacher.students.show', compact('student'));
    }

    public function excuses()
    {
        $excuses = collect();
        return view('teacher.excuses.index', compact('excuses'));
    }

    public function showExcuse($id)
    {
        $excuse = null;
        return view('teacher.excuses.show', compact('excuse'));
    }

    public function approveExcuse($id)
    {
        return redirect()->route('teacher.excuses');
    }

    public function rejectExcuse($id)
    {
        return redirect()->route('teacher.excuses');
    }

    public function reports()
    {
        $reportData = collect();
        return view('teacher.reports.index', compact('reportData'));
    }
}

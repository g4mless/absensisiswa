<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with(['attendanceSession.class', 'student.user', 'student.class'])
            ->orderByDesc('created_at')
            ->paginate(15);
        $classes = ClassModel::orderBy('name')->get();
        return view('admin.attendance.index', compact('attendances', 'classes'));
    }
}

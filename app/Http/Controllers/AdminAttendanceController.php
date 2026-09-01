<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\DailyAttendance;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyAttendance::with(['student.user', 'student.class'])
            ->when($request->filled('date'), fn ($query) => $query->whereDate('date', $request->input('date')))
            ->when($request->filled('class_id'), fn ($query) => $query->whereHas('student', fn ($student) => $student->where('class_id', $request->input('class_id'))))
            ->orderByDesc('date');
        $attendances = $query->paginate(15);
        $classes = ClassModel::orderBy('name')->get();
        return view('admin.attendance.index', compact('attendances', 'classes'));
    }
}

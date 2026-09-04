<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\DailyAttendance;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ?: now()->format('Y-m-d');
        $query = DailyAttendance::with(['student.user', 'student.class'])
            ->whereDate('date', $date)
            ->when($request->filled('class_id'), fn ($query) => $query->whereHas('student', fn ($student) => $student->where('class_id', $request->input('class_id'))))
            ->orderByDesc('date');
        $attendances = $query->paginate(15);
        $classes = ClassModel::orderBy('name')->get();
        return view('admin.attendance.index', compact('attendances', 'classes', 'date'));
    }
}

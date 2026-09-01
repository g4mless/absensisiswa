<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with(['class', 'subject', 'teacher.user'])->orderBy('day')->paginate(15);
        $classes = ClassModel::orderBy('name')->get();
        return view('admin.schedules.index', compact('schedules', 'classes'));
    }

    public function create()
    {
        return view('admin.schedules.create', [
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => Teacher::with('user')->orderBy('nip')->get(),
            'classes' => ClassModel::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Schedule::create($this->validated($request));
        return redirect()->route('admin.schedules.index');
    }

    public function show($id)
    {
        $item = Schedule::with(['class', 'subject', 'teacher.user'])->findOrFail($id);
        return view('admin.schedules.show', compact('item'));
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        return view('admin.schedules.edit', [
            'schedule' => $schedule,
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => Teacher::with('user')->orderBy('nip')->get(),
            'classes' => ClassModel::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update($this->validated($request));
        return redirect()->route('admin.schedules.index');
    }

    public function destroy($id)
    {
        Schedule::findOrFail($id)->delete();
        return redirect()->route('admin.schedules.index');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'day' => ['required', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:255'],
        ]);
    }
}

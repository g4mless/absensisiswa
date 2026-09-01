<?php

namespace App\Http\Controllers;

use App\Models\TeacherSubject;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherSubjectController extends Controller
{
    public function index()
    {
        $teacherSubjects = TeacherSubject::with(['teacher.user', 'subject', 'class'])->paginate(15);
        return view('admin.teacher-subjects.index', compact('teacherSubjects'));
    }

    public function create()
    {
        return view('admin.teacher-subjects.create', [
            'teachers' => Teacher::with('user')->orderBy('nip')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'classes' => ClassModel::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ]);
        DB::transaction(function () use ($data) {
            foreach ($data['class_ids'] as $classId) {
                TeacherSubject::create([
                    'teacher_id' => $data['teacher_id'],
                    'subject_id' => $data['subject_id'],
                    'class_id' => $classId,
                ]);
            }
        });
        return redirect()->route('admin.teacher-subjects.index');
    }

    public function destroy($id)
    {
        TeacherSubject::findOrFail($id)->delete();
        return redirect()->route('admin.teacher-subjects.index');
    }
}

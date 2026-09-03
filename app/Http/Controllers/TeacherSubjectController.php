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

    public function edit($id)
    {
        return view('admin.teacher-subjects.edit', [
            'assignment' => TeacherSubject::findOrFail($id),
            'teachers' => Teacher::with('user')->orderBy('nip')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'classes' => ClassModel::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_name' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ]);
        $teacher = $this->findTeacherByName($data['teacher_name']);
        if (!$teacher) {
            return back()->withErrors(['teacher_name' => 'Guru dengan nama tersebut tidak ditemukan.'])->withInput();
        }
        DB::transaction(function () use ($data, $teacher) {
            foreach ($data['class_ids'] as $classId) {
                TeacherSubject::create([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $data['subject_id'],
                    'class_id' => $classId,
                ]);
            }
        });
        return redirect()->route('admin.teacher-subjects.index');
    }

    public function update(Request $request, $id)
    {
        $assignment = TeacherSubject::findOrFail($id);
        $data = $request->validate([
            'teacher_name' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
        ]);
        $teacher = $this->findTeacherByName($data['teacher_name']);
        if (!$teacher) {
            return back()->withErrors(['teacher_name' => 'Guru dengan nama tersebut tidak ditemukan.'])->withInput();
        }

        $assignment->update([
            'teacher_id' => $teacher->id,
            'subject_id' => $data['subject_id'],
            'class_id' => $data['class_id'],
        ]);

        return redirect()->route('admin.teacher-subjects.index')->with('success', 'Penugasan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        TeacherSubject::findOrFail($id)->delete();
        return redirect()->route('admin.teacher-subjects.index');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $count = TeacherSubject::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.teacher-subjects.index')->with('status', $count . ' penugasan berhasil dihapus.');
    }

    private function findTeacherByName(string $name): ?Teacher
    {
        return Teacher::whereHas('user', function ($query) use ($name) {
            $query->whereRaw('LOWER(name) = ?', [strtolower(trim($name))]);
        })->first();
    }
}

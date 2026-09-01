<?php

namespace App\Http\Controllers;

use App\Models\HomeroomTeacher;
use App\Models\ClassModel;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeroomTeacherController extends Controller
{
    public function index()
    {
        $homeroomTeachers = HomeroomTeacher::with(['teacher.user', 'class'])->paginate(15);
        return view('admin.homeroom-teachers.index', compact('homeroomTeachers'));
    }

    public function create()
    {
        return view('admin.homeroom-teachers.create', [
            'teachers' => Teacher::with('user')->orderBy('nip')->get(),
            'classes' => ClassModel::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required', 'exists:classes,id'],
        ]);
        HomeroomTeacher::create($data);
        return redirect()->route('admin.homeroom-teachers.index');
    }

    public function destroy($id)
    {
        HomeroomTeacher::findOrFail($id)->delete();
        return redirect()->route('admin.homeroom-teachers.index');
    }
}

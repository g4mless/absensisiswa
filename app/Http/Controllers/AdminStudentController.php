<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminStudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['user', 'class'])->orderBy('nis')->paginate(15);
        $classes = ClassModel::orderBy('name')->get();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $classes = ClassModel::orderBy('name')->get();

        return view('admin.students.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'unique:students,nis'],
            'name' => ['required', 'string'],
            'class_id' => ['required', 'exists:classes,id'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'is_pkl' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['name'].'-'.$data['nis'],
                'email' => $data['email'] ?? $data['nis'].'@student.local',
                'role' => ! empty($data['is_pkl']) ? 'siswa_pkl' : 'siswa',
                'password' => $data['nis'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            Student::create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'class_id' => $data['class_id'],
                'is_pkl' => ! empty($data['is_pkl']),
                'is_active' => $data['is_active'] ?? true,
            ]);
        });

        return redirect()->route('admin.students.index')
            ->with('status', 'Siswa berhasil ditambahkan. Login menggunakan nama lengkap dan NIS.');
    }

    public function show($id)
    {
        $item = Student::with(['user', 'class'])->findOrFail($id);

        return view('admin.students.show', compact('item'));
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $classes = ClassModel::orderBy('name')->get();

        return view('admin.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::with('user')->findOrFail($id);
        $data = $request->validate([
            'nis' => ['required', 'string', Rule::unique('students', 'nis')->ignore($student->id)],
            'name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($student->user_id)],
            'is_pkl' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        DB::transaction(function () use ($student, $data, $request) {
            $active = $request->boolean('is_active');
            $isPkl = $request->boolean('is_pkl');
            $student->user->update([
                'name' => $data['name'],
                'email' => $data['email'] ?? $student->user->email,
                'role' => $isPkl ? 'siswa_pkl' : 'siswa',
                'is_active' => $active,
            ]);
            $student->update([
                'nis' => $data['nis'], 'class_id' => $data['class_id'],
                'is_pkl' => $isPkl, 'is_active' => $active,
            ]);
        });
        return redirect()->route('admin.students.index');
    }

    public function destroy($id)
    {
        Student::findOrFail($id)->delete();
        return redirect()->route('admin.students.index');
    }
}

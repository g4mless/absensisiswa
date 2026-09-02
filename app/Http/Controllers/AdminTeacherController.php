<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminTeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with(['user', 'subjects'])->orderBy('nip')->paginate(15);
        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create', ['subjects' => Subject::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nip' => ['required', 'string', 'max:255', 'unique:teachers,nip'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
        ]);

        DB::transaction(function () use ($data) {
            $user = \App\Models\User::create([
                'name' => $data['name'],
                'username' => $data['nip'],
                'email' => $data['email'] ?? $data['nip'].'@teacher.local',
                'role' => 'guru',
                'password' => Hash::make($data['nip']),
            ]);
            Teacher::create([
                'user_id' => $user->id,
                'nip' => $data['nip'],
            ]);
        });

        return redirect()->route('admin.teachers.index');
    }

    public function show($id)
    {
        $item = Teacher::with(['user', 'subjects'])->findOrFail($id);
        return view('admin.teachers.show', compact('item'));
    }

    public function edit($id)
    {
        $teacher = Teacher::with('subjects')->findOrFail($id);
        return view('admin.teachers.edit', [
            'teacher' => $teacher,
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        $data = $request->validate([
            'nip' => ['required', 'string', 'max:255', Rule::unique('teachers', 'nip')->ignore($teacher->id)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher->user_id)],
        ]);
        DB::transaction(function () use ($teacher, $data, $request) {
            $teacher->user->update([
                'name' => $data['name'],
                'email' => $data['email'] ?? $teacher->user->email,
            ]);
            $teacher->update(['nip' => $data['nip']]);
        });
        return redirect()->route('admin.teachers.index');
    }

    public function destroy($id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        DB::transaction(fn () => $teacher->user->delete());
        return redirect()->route('admin.teachers.index');
    }
}

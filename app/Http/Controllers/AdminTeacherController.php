<?php

namespace App\Http\Controllers;

use App\Exports\TeacherExport;
use App\Imports\TeacherImport;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AdminTeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with(['user', 'subjects', 'programHead.major'])->orderBy('nip')->paginate(15);
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
        ]);

        DB::transaction(function () use ($data) {
            $user = \App\Models\User::create([
                'name' => $data['name'],
                'username' => $data['nip'],
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
        ]);
        DB::transaction(function () use ($teacher, $data, $request) {
            $teacher->user->update([
                'name' => $data['name'],
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

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $teachers = Teacher::whereIn('id', $request->ids)->with('user')->get();
        DB::transaction(function () use ($teachers) {
            foreach ($teachers as $teacher) {
                $teacher->user?->delete();
            }
        });
        return redirect()->route('admin.teachers.index')->with('status', count($teachers) . ' guru berhasil dihapus.');
    }

    public function allDestroy()
    {
        $teachers = Teacher::with('user')->get();
        DB::transaction(function () use ($teachers) {
            foreach ($teachers as $teacher) {
                $teacher->user?->delete();
            }
        });
        return redirect()->route('admin.teachers.index')->with('status', $teachers->count() . ' guru berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new TeacherImport, $request->file('file'));

        return redirect()->route('admin.teachers.index')
            ->with('status', 'Data guru berhasil diimpor.');
    }

    public function export()
    {
        return Excel::download(new TeacherExport, 'data_guru.xlsx');
    }
}

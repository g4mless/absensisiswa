<?php

namespace App\Http\Controllers;

use App\Exports\StudentExport;
use App\Imports\StudentMultiSheetImport;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

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
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_pkl' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['name'].'-'.$data['nis'],
                'role' => ! empty($data['is_pkl']) ? 'siswa_pkl' : 'siswa',
                'password' => $data['nis'],
            ]);

            Student::create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'class_id' => $data['class_id'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_pkl' => ! empty($data['is_pkl']),
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
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_pkl' => ['nullable', 'boolean'],
        ]);
        DB::transaction(function () use ($student, $data, $request) {
            $isPkl = $request->boolean('is_pkl');
            $student->user->update([
                'name' => $data['name'],
                'role' => $isPkl ? 'siswa_pkl' : 'siswa',
            ]);
            $student->update([
                'nis' => $data['nis'], 'class_id' => $data['class_id'],
                'phone' => $data['phone'] ?? null, 'address' => $data['address'] ?? null,
                'is_pkl' => $isPkl,
            ]);
        });
        return redirect()->route('admin.students.index');
    }

    public function destroy($id)
    {
        $student = Student::with('user')->findOrFail($id);
        DB::transaction(fn () => $student->user->delete());
        return redirect()->route('admin.students.index');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $students = Student::whereIn('id', $request->ids)->with('user')->get();
        DB::transaction(function () use ($students) {
            foreach ($students as $student) {
                $student->user?->delete();
            }
        });
        return redirect()->route('admin.students.index')->with('status', count($students) . ' siswa berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new StudentMultiSheetImport, $request->file('file'));

        return redirect()->route('admin.students.index')
            ->with('status', 'Data siswa berhasil diimpor.');
    }

    public function export()
    {
        return Excel::download(new StudentExport, 'data_siswa.xlsx');
    }
}

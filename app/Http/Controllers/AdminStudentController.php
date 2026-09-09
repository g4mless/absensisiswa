<?php

namespace App\Http\Controllers;

use App\Exports\StudentExport;
use App\Imports\StudentMultiSheetImport;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentPkl;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AdminStudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with(['user', 'class', 'pkl.pembimbing.user'])
            ->when($request->filled('search'), fn ($query) => $query
                ->where('nis', 'like', '%'.$request->input('search').'%')
                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$request->input('search').'%')))
            ->orderBy('nis')
            ->paginate(15)
            ->withQueryString();
        $classes = ClassModel::with('major')->orderBy('grade')->orderBy('major_id')->orderBy('section')->get();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $classes = ClassModel::with('major')->orderBy('grade')->orderBy('major_id')->orderBy('section')->get();
        $teachers = Teacher::with('user')->orderBy('nip')->get();

        return view('admin.students.create', compact('classes', 'teachers'));
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
            'tempat_pkl' => ['nullable', 'string', 'max:255'],
            'pembimbing_id' => ['nullable', 'exists:teachers,id'],
            'pkl_start_date' => ['nullable', 'date'],
            'pkl_end_date' => ['nullable', 'date', 'after_or_equal:pkl_start_date'],
            'pkl_status' => ['nullable', Rule::in(['PLANNED', 'ACTIVE', 'COMPLETED', 'CANCELLED'])],
        ]);

        DB::transaction(function () use ($data, $request) {
            $isPkl = $request->boolean('is_pkl');
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['name'].'-'.$data['nis'],
                'role' => $isPkl ? 'siswa_pkl' : 'siswa',
                'password' => $data['nis'],
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'class_id' => $data['class_id'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_pkl' => $isPkl,
            ]);

            // Detail penempatan PKL (pengganti menu pkl-placements yang dihapus).
            if ($isPkl && ! empty($data['tempat_pkl'])) {
                StudentPkl::create([
                    'student_id' => $student->id,
                    'tempat_pkl' => $data['tempat_pkl'],
                    'pembimbing_id' => $data['pembimbing_id'] ?? null,
                    'start_date' => $data['pkl_start_date'] ?? null,
                    'end_date' => $data['pkl_end_date'] ?? null,
                    'status' => $data['pkl_status'] ?? 'ACTIVE',
                ]);
            }
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
        $student = Student::with('pkl')->findOrFail($id);
        $classes = ClassModel::with('major')->orderBy('grade')->orderBy('major_id')->orderBy('section')->get();
        $teachers = Teacher::with('user')->orderBy('nip')->get();

        return view('admin.students.edit', compact('student', 'classes', 'teachers'));
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
            'tempat_pkl' => ['nullable', 'string', 'max:255'],
            'pembimbing_id' => ['nullable', 'exists:teachers,id'],
            'pkl_start_date' => ['nullable', 'date'],
            'pkl_end_date' => ['nullable', 'date', 'after_or_equal:pkl_start_date'],
            'pkl_status' => ['nullable', Rule::in(['PLANNED', 'ACTIVE', 'COMPLETED', 'CANCELLED'])],
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

            // Detail penempatan PKL (pengganti menu pkl-placements yang dihapus).
            if ($isPkl && ! empty($data['tempat_pkl'])) {
                StudentPkl::updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'tempat_pkl' => $data['tempat_pkl'],
                        'pembimbing_id' => $data['pembimbing_id'] ?? null,
                        'start_date' => $data['pkl_start_date'] ?? null,
                        'end_date' => $data['pkl_end_date'] ?? null,
                        'status' => $data['pkl_status'] ?? 'ACTIVE',
                    ]
                );
            } elseif (! $isPkl) {
                StudentPkl::where('student_id', $student->id)->delete();
            }
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

    public function allDestroy()
    {
        $students = Student::with('user')->get();
        DB::transaction(function () use ($students) {
            foreach ($students as $student) {
                $student->user?->delete();
            }
        });
        return redirect()->route('admin.students.index')->with('status', $students->count() . ' siswa berhasil dihapus.');
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

<?php

namespace App\Http\Controllers;

use App\Models\ProgramHead;
use App\Models\Major;
use App\Models\Teacher;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class ProgramHeadController extends Controller
{
    public function index()
    {
        $programHeads = ProgramHead::with(['teacher.user', 'major', 'academicYear'])->paginate(15);
        return view('admin.program-heads.index', compact('programHeads'));
    }

    public function create()
    {
        return view('admin.program-heads.create', [
            'teachers' => Teacher::with('user')->orderBy('nip')->get(),
            'majors' => Major::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'major_id' => ['required', 'exists:majors,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
        ]);
        $data['academic_year_id'] ??= AcademicYear::where('is_active', true)->value('id');
        if (! $data['academic_year_id']) {
            return back()->withErrors(['academic_year_id' => 'Belum ada tahun akademik aktif.'])->withInput();
        }
        ProgramHead::create($data);
        return redirect()->route('admin.program-heads.index');
    }

    public function destroy($id)
    {
        ProgramHead::findOrFail($id)->delete();
        return redirect()->route('admin.program-heads.index');
    }
}

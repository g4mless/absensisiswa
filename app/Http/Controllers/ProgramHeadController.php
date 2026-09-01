<?php

namespace App\Http\Controllers;

use App\Models\ProgramHead;
use App\Models\Major;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ProgramHeadController extends Controller
{
    public function index()
    {
        $programHeads = ProgramHead::with(['teacher.user', 'major'])->paginate(15);
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
        ]);
        ProgramHead::create($data);
        return redirect()->route('admin.program-heads.index');
    }

    public function destroy($id)
    {
        ProgramHead::findOrFail($id)->delete();
        return redirect()->route('admin.program-heads.index');
    }
}

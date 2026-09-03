<?php

namespace App\Http\Controllers;

use App\Models\PklSupervisor;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class PklSupervisorController extends Controller
{
    public function index()
    {
        $pklSupervisors = PklSupervisor::with(['teacher.user', 'student.user'])->paginate(15);
        return view('admin.pkl-supervisors.index', compact('pklSupervisors'));
    }

    public function create()
    {
        return view('admin.pkl-supervisors.create', [
            'teachers' => Teacher::with('user')->orderBy('nip')->get(),
            'students' => Student::with('user')->orderBy('nis')->get(),
        ]);
    }

    public function edit($id)
    {
        return view('admin.pkl-supervisors.edit', [
            'assignment' => PklSupervisor::findOrFail($id),
            'teachers' => Teacher::with('user')->orderBy('nip')->get(),
            'students' => Student::with('user')->orderBy('nis')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'exists:students,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
        ]);
        $teacher = $this->findTeacherByName($data['teacher_name']);
        if (!$teacher) {
            return back()->withErrors(['teacher_name' => 'Guru dengan nama tersebut tidak ditemukan.'])->withInput();
        }
        PklSupervisor::create([
            'teacher_id' => $teacher->id,
            'student_id' => $data['student_id'],
            'company_name' => $data['company_name'],
            'company_address' => $data['company_address'],
        ]);
        return redirect()->route('admin.pkl-supervisors.index');
    }

    public function update(Request $request, $id)
    {
        $assignment = PklSupervisor::findOrFail($id);
        $data = $request->validate([
            'teacher_name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'exists:students,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
        ]);
        $teacher = $this->findTeacherByName($data['teacher_name']);
        if (!$teacher) {
            return back()->withErrors(['teacher_name' => 'Guru dengan nama tersebut tidak ditemukan.'])->withInput();
        }
        $assignment->update([
            'teacher_id' => $teacher->id,
            'student_id' => $data['student_id'],
            'company_name' => $data['company_name'],
            'company_address' => $data['company_address'],
        ]);

        return redirect()->route('admin.pkl-supervisors.index')->with('success', 'Penugasan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        PklSupervisor::findOrFail($id)->delete();
        return redirect()->route('admin.pkl-supervisors.index');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $count = PklSupervisor::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.pkl-supervisors.index')->with('status', $count . ' penugasan berhasil dihapus.');
    }

    public function allDestroy()
    {
        $count = PklSupervisor::query()->delete();
        return redirect()->route('admin.pkl-supervisors.index')->with('status', $count . ' penugasan berhasil dihapus.');
    }

    public function supervisorDashboard()
    {
        $assignedStudents = collect();
        $gpsAlerts = collect();
        return view('pkl-supervisor.dashboard', compact('assignedStudents', 'gpsAlerts'));
    }

    public function students()
    {
        $students = collect();
        return view('pkl-supervisor.students.index', compact('students'));
    }

    public function showStudent($id)
    {
        $attendances = collect();
        $locations = collect();
        return view('pkl-supervisor.students.show', compact('attendances', 'locations'));
    }

    public function attendance()
    {
        $attendances = collect();
        return view('pkl-supervisor.attendance.index', compact('attendances'));
    }

    public function locations()
    {
        $studentLocations = collect();
        $outsideRadius = collect();
        return view('pkl-supervisor.locations.index', compact('studentLocations', 'outsideRadius'));
    }

    public function showLocation($studentId)
    {
        $locations = collect();
        return view('pkl-supervisor.locations.show', compact('locations'));
    }

    private function findTeacherByName(string $name): ?Teacher
    {
        return Teacher::whereHas('user', function ($query) use ($name) {
            $query->whereRaw('LOWER(name) = ?', [strtolower(trim($name))]);
        })->first();
    }
}

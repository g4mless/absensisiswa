<?php

namespace App\Http\Controllers;

use App\Models\PklSupervisor;
use Illuminate\Http\Request;

class PklSupervisorController extends Controller
{
    public function index()
    {
        $pklSupervisors = PklSupervisor::orderBy('supervisor_name')->paginate(15);
        return view('admin.pkl-supervisors.index', compact('pklSupervisors'));
    }

    public function create()
    {
        return view('admin.pkl-supervisors.create');
    }

    public function edit($id)
    {
        return view('admin.pkl-supervisors.edit', [
            'assignment' => PklSupervisor::findOrFail($id),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supervisor_name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['required', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
        ]);
        PklSupervisor::create($data);
        return redirect()->route('admin.pkl-supervisors.index');
    }

    public function update(Request $request, $id)
    {
        $assignment = PklSupervisor::findOrFail($id);
        $data = $request->validate([
            'supervisor_name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['required', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
        ]);
        $assignment->update($data);

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

}

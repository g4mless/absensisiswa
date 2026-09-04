<?php

namespace App\Http\Controllers;

use App\Models\PklSupervisor;
use App\Models\ClassModel;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class PklSupervisorController extends Controller
{
    public function index()
    {
        $pklSupervisors = PklSupervisor::with(['teacher.user', 'class.major'])->orderBy('id')->paginate(15);
        return view('admin.pkl-supervisors.index', compact('pklSupervisors'));
    }

    public function transfer()
    {
        return view('admin.data-transfer');
    }

    public function create()
    {
        return view('admin.pkl-supervisors.create', $this->formData());
    }

    public function edit($id)
    {
        return view('admin.pkl-supervisors.edit', [
            'assignment' => PklSupervisor::findOrFail($id),
            ...$this->formData(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required', 'exists:classes,id'],
        ]);
        PklSupervisor::create($data);
        return redirect()->route('admin.pkl-supervisors.index');
    }

    public function update(Request $request, $id)
    {
        $assignment = PklSupervisor::findOrFail($id);
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required', 'exists:classes,id'],
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

    public function allDestroy()
    {
        $count = PklSupervisor::query()->delete();
        return redirect()->route('admin.pkl-supervisors.index')->with('status', $count . ' penugasan berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx']]);

        try {
            $result = (new \App\Imports\TeacherImport)->import($request->file('file'), ['PKL']);
        } catch (Throwable $exception) {
            return redirect()->route('admin.pkl-supervisors.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.pkl-supervisors.index')
            ->with('success', "{$result['teachers']} guru pembimbing dan penugasannya berhasil diimpor dari worksheet PKL.");
    }

    public function export()
    {
        return Excel::download(new \App\Exports\PklSupervisorExport, 'pembimbing_pkl.xlsx');
    }

    private function formData(): array
    {
        return [
            'teachers' => Teacher::with('user')->orderBy('nip')->get(),
            'classes' => ClassModel::with('major')->orderBy('grade')->orderBy('major_id')->orderBy('section')->get(),
        ];
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

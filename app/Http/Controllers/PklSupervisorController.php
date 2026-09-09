<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\PklLocation;
use App\Models\PklLocationLog;
use App\Models\PklSupervisor;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentPkl;
use App\Models\StudentLocation;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

    public function supervisorDashboard(Request $request)
    {
        $ids = $this->supervisedStudentIds();
        $today = today()->toDateString();

        $assignedStudents = Student::with(['user', 'class', 'pkl.pembimbing.user'])
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->limit(10)
            ->get()
            ->each(function ($student) use ($today) {
                $student->todayAttendance = $this->todayAttendanceFor($student->id, $today);
                $latest = $this->latestLocationFor($student->id);
                $student->lastGpsTime = $latest?->recorded_at ?? $latest?->created_at;
            });

        $totalStudents = count($ids);
        $activePkl = $this->supervisedPklQuery()?->where('status', 'ACTIVE')->count() ?? $totalStudents;
        $todayAttendance = $this->hasDailyTable()
            ? DailyAttendance::whereIn('student_id', $ids)->where('date', $today)->count()
            : 0;
        $gpsAlerts = $this->gpsAlerts($ids);
        $issues = $gpsAlerts->count();

        return view('pkl-supervisor.dashboard', compact(
            'assignedStudents', 'gpsAlerts', 'totalStudents', 'activePkl', 'todayAttendance', 'issues'
        ));
    }

    public function students(Request $request)
    {
        $ids = $this->supervisedStudentIds();

        $query = Student::with(['user', 'class', 'pkl.pembimbing.user'])
            ->whereIn('id', $ids)
            ->when($this->hasStudentPklTable(), fn ($q) => $q->whereHas('pkl', fn ($p) => $p->where('status', 'ACTIVE')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%'.$request->input('search').'%';
                $q->where(fn ($w) => $w->where('nis', 'like', $search)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $search)));
            })
            ->orderBy('id');

        $students = $query->paginate(15)->withQueryString();
        $students->getCollection()->each(function ($student) {
            $latest = $this->latestLocationFor($student->id);
            $student->lastGpsTime = $latest?->recorded_at ?? $latest?->created_at;
        });

        return view('pkl-supervisor.students.index', compact('students'));
    }

    public function showStudent($id)
    {
        $this->authorizeSupervised((int) $id);

        $student = Student::with(['user', 'class', 'pkl.pembimbing.user'])->findOrFail($id);
        $attendances = $this->hasDailyTable()
            ? DailyAttendance::where('student_id', $id)->orderByDesc('date')->limit(20)->get()
            : collect();
        $locations = $this->locationHistoryFor($id, 20);

        return view('pkl-supervisor.students.show', compact('student', 'attendances', 'locations'));
    }

    public function attendance(Request $request)
    {
        $ids = $this->supervisedStudentIds();
        $studentId = $request->input('student_id');

        if ($request->filled('student_id') && ! in_array((int) $studentId, $ids, true)) {
            abort(403, 'Bukan siswa bimbingan Anda.');
        }

        $attendances = $this->hasDailyTable()
            ? DailyAttendance::with('student.user')
                ->whereIn('student_id', $ids)
                ->when($request->filled('student_id'), fn ($q) => $q->where('student_id', $studentId))
                ->when($request->filled('start_date') || $request->filled('date'), fn ($q) => $q->where('date', '>=', $request->input('start_date', $request->input('date'))))
                ->when($request->filled('end_date'), fn ($q) => $q->where('date', '<=', $request->input('end_date')))
                ->orderByDesc('date')
                ->paginate(15)
                ->withQueryString()
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

        $studentOptions = Student::with('user')->whereIn('id', $ids)->orderBy('id')->get()
            ->mapWithKeys(fn ($s) => [$s->id => ($s->user->name ?? '-')]);

        return view('pkl-supervisor.attendance.index', compact('attendances', 'studentOptions'));
    }

    public function locations()
    {
        $ids = $this->supervisedStudentIds();

        $studentLocations = collect($ids)
            ->map(fn ($id) => $this->latestLocationFor((int) $id))
            ->filter()
            ->values();

        $outsideRadius = $studentLocations
            ->filter(fn ($loc) => $this->isOutsideRadius($loc))
            ->map(function ($loc) {
                $loc->message = $this->locationFlagMessage($loc);
                return $loc;
            })
            ->values();

        return view('pkl-supervisor.locations.index', compact('studentLocations', 'outsideRadius'));
    }

    public function showLocation($studentId)
    {
        $this->authorizeSupervised((int) $studentId);

        $student = Student::with(['user', 'class', 'pkl.pembimbing.user'])->findOrFail($studentId);
        $today = today()->toDateString();
        $student->todayAttendance = $this->todayAttendanceFor($student->id, $today);
        $student->lastLocation = $this->latestLocationFor($student->id);
        $locations = $this->locationHistoryFor($student->id, 15, true);

        return view('pkl-supervisor.locations.show', compact('student', 'locations'));
    }

    // ── Helpers (scope pembimbing, anti IDOR) ──────────────────────────────

    private function currentTeacher(): ?Teacher
    {
        return auth()->user()?->teacher;
    }

    private function hasStudentPklTable(): bool
    {
        if (! class_exists(StudentPkl::class)) {
            return false;
        }
        try {
            return Schema::hasTable('student_pkl');
        } catch (Throwable) {
            return false;
        }
    }

    private function hasDailyTable(): bool
    {
        try {
            return Schema::hasTable('daily_attendances');
        } catch (Throwable) {
            return false;
        }
    }

    /** ID siswa yang dibimbing guru login: via student_pkl.pembimbing_id + fallback kelas assignment. */
    private function supervisedStudentIds(): array
    {
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            return [];
        }

        $ids = [];
        if ($this->hasStudentPklTable()) {
            $ids = array_merge($ids, StudentPkl::where('pembimbing_id', $teacher->id)->pluck('student_id')->all());
        }

        $classIds = PklSupervisor::where('teacher_id', $teacher->id)->pluck('class_id')->all();
        if ($classIds !== []) {
            $ids = array_merge($ids, Student::whereIn('class_id', $classIds)->where('is_pkl', true)->pluck('id')->all());
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function supervisedPklQuery(): mixed
    {
        $teacher = $this->currentTeacher();
        if (! $teacher || ! $this->hasStudentPklTable()) {
            return null;
        }

        return StudentPkl::where('pembimbing_id', $teacher->id);
    }

    private function authorizeSupervised(int $studentId): void
    {
        if (! in_array($studentId, $this->supervisedStudentIds(), true)) {
            abort(403, 'Bukan siswa bimbingan Anda.');
        }
    }

    private function todayAttendanceFor(int $studentId, string $today): mixed
    {
        if (! $this->hasDailyTable()) {
            return null;
        }

        return DailyAttendance::where('student_id', $studentId)->where('date', $today)->first();
    }

    /** Sumber lokasi prioritas: pkl_location_logs → pkl_locations → student_locations. */
    private function latestLocationFor(int $studentId): mixed
    {
        if (class_exists(PklLocationLog::class)) {
            try {
                if (Schema::hasTable('pkl_location_logs')) {
                    $log = PklLocationLog::with('student.user')->where('student_id', $studentId)->orderByDesc('recorded_at')->first();
                    if ($log) {
                        return $log;
                    }
                }
            } catch (Throwable) {
            }
        }

        foreach ([PklLocation::class, StudentLocation::class] as $model) {
            try {
                $table = (new $model)->getTable();
                if (! Schema::hasTable($table)) {
                    continue;
                }
                $loc = $model::with('student.user')->where('student_id', $studentId)->latest()->first();
                if ($loc) {
                    return $loc;
                }
            } catch (Throwable) {
            }
        }

        return null;
    }

    private function locationHistoryFor(int $studentId, int $limit = 20, bool $paginate = false): mixed
    {
        if (class_exists(PklLocationLog::class)) {
            try {
                if (Schema::hasTable('pkl_location_logs')) {
                    $query = PklLocationLog::where('student_id', $studentId)->orderByDesc('recorded_at');
                    return $paginate ? $query->paginate(15)->withQueryString() : $query->limit($limit)->get();
                }
            } catch (Throwable) {
            }
        }

        foreach ([PklLocation::class, StudentLocation::class] as $model) {
            try {
                if (! Schema::hasTable((new $model)->getTable())) {
                    continue;
                }
                $query = $model::where('student_id', $studentId)->latest();
                return $paginate ? $query->paginate(15)->withQueryString() : $query->limit($limit)->get();
            } catch (Throwable) {
            }
        }

        return $paginate
            ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15)
            : collect();
    }

    /** Flag jika lokasi stale (>30 menit) atau akurasi buruk (>50 m). */
    private function isOutsideRadius(mixed $loc): bool
    {
        $time = $loc->recorded_at ?? $loc->created_at ?? null;

        if (! $time || \Carbon\Carbon::parse($time)->lt(now()->subMinutes(30))) {
            return true;
        }

        return $loc->accuracy !== null && (float) $loc->accuracy > 50;
    }

    private function locationFlagMessage(mixed $loc): string
    {
        $time = $loc->recorded_at ?? $loc->created_at ?? null;

        if (! $time || \Carbon\Carbon::parse($time)->lt(now()->subMinutes(30))) {
            return 'Tidak mengirim lokasi >30 menit.';
        }

        return 'Akurasi GPS buruk ('.number_format((float) $loc->accuracy, 1).' m).';
    }

    /** Siswa bimbingan tanpa lokasi >30 menit (atau belum pernah) = gpsAlerts dashboard. */
    private function gpsAlerts(array $ids): \Illuminate\Support\Collection
    {
        return collect($ids)
            ->map(fn ($id) => Student::with('user')->find($id))
            ->filter()
            ->filter(function ($student) {
                $latest = $this->latestLocationFor($student->id);
                $time = $latest?->recorded_at ?? $latest?->created_at;

                return ! $time || \Carbon\Carbon::parse($time)->lt(now()->subMinutes(30));
            })
            ->map(function ($student) {
                $latest = $this->latestLocationFor($student->id);
                $time = $latest?->recorded_at ?? $latest?->created_at;

                return (object) [
                    'student' => $student,
                    'message' => $time
                        ? 'Lokasi terakhir '.\Carbon\Carbon::parse($time)->diffForHumans().'.'
                        : 'Belum pernah mengirim lokasi.',
                    'created_at' => $time ?? now(),
                ];
            })
            ->values();
    }

}

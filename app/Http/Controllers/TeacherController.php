<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\DailyAttendance;
use App\Models\Excuse;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    private function currentTeacher()
    {
        return auth()->user()?->teacher;
    }

    private function teacherClassIds(): array
    {
        $teacher = $this->currentTeacher();
        if (! $teacher) {
            return [];
        }

        return Schedule::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id')
            ->all();
    }

    private function normalizeStatus(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        return strtolower($status);
    }

    public function dashboard()
    {
        $teacher = $this->currentTeacher();
        $todayDay = now()->format('l'); // Monday..Friday
        $nowTime = now()->format('H:i:s');

        $todaySchedule = $teacher
            ? Schedule::with(['class.major', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('day', $todayDay)
                ->orderBy('start_time')
                ->get()
                ->each(function ($session) use ($nowTime) {
                    $session->is_current = $session->start_time <= $nowTime && $nowTime <= $session->end_time;
                    // Alias agar blade lama ($session->classroom) tetap jalan.
                    $session->setRelation('classroom', $session->class);
                })
            : collect();

        $classIds = $this->teacherClassIds();
        $totalClasses = count($classIds);
        $todaySessions = $todaySchedule->count();

        $studentIds = $classIds
            ? \App\Models\Student::whereIn('class_id', $classIds)->pluck('id')
            : collect();

        $todayAttendances = $studentIds->count()
            ? DailyAttendance::whereIn('student_id', $studentIds)->whereDate('date', today())->get()
            : collect();

        $studentsPresentToday = $todayAttendances->where('status', 'hadir')->count();
        $totalStudents = $studentIds->count();
        $attendanceRate = $totalStudents > 0
            ? round($studentsPresentToday / $totalStudents * 100).'%'
            : '0%';

        $pendingExcuses = $classIds
            ? Excuse::whereHas('student', fn ($q) => $q->whereIn('class_id', $classIds))
                ->where('status', 'pending')
                ->count()
            : 0;

        $recentExcuses = $classIds
            ? Excuse::with(['student.user', 'student.class'])
                ->whereHas('student', fn ($q) => $q->whereIn('class_id', $classIds))
                ->orderByDesc('date')
                ->limit(5)
                ->get()
            : collect();

        return view('teacher.dashboard', compact(
            'todaySchedule', 'recentExcuses', 'totalClasses',
            'todaySessions', 'studentsPresentToday', 'pendingExcuses', 'attendanceRate'
        ));
    }

    public function schedule()
    {
        $teacher = $this->currentTeacher();

        $schedules = $teacher
            ? Schedule::with(['class.major', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->orderBy('start_time')
                ->get()
                ->each(fn ($s) => $s->setRelation('classroom', $s->class))
            : collect();

        // Kelompokkan slot waktu unik (start-end) sebagai baris tabel.
        $grouped = $schedules->groupBy(fn ($s) => substr($s->start_time, 0, 5).'-'.substr($s->end_time, 0, 5));

        $timeSlots = $grouped->map(function ($items, $key) {
            $first = $items->first();

            return (object) [
                'id' => md5($key),
                'key' => $key,
                'start_time' => substr($first->start_time, 0, 5),
                'end_time' => substr($first->end_time, 0, 5),
            ];
        })->values();

        $keyToId = $grouped->mapWithKeys(fn ($items, $key) => [$key => md5($key)]);

        $schedule = [];
        foreach ($schedules as $session) {
            $dayKey = strtolower($session->day); // monday..friday
            $timeKey = substr($session->start_time, 0, 5).'-'.substr($session->end_time, 0, 5);
            $slotId = $keyToId[$timeKey] ?? null;
            if ($slotId) {
                $schedule[$dayKey][$slotId] = $session;
            }
        }

        return view('teacher.schedule', compact('timeSlots', 'schedule'));
    }

    public function classes()
    {
        $classIds = $this->teacherClassIds();
        $teacherId = $this->currentTeacher()?->id;

        $classes = $classIds
            ? ClassModel::with(['major', 'homeroomTeacher.teacher.user'])
                ->withCount('students')
                ->whereIn('id', $classIds)
                ->get()
                ->each(function ($class) use ($teacherId) {
                    $class->schedule_count = Schedule::where('class_id', $class->id)
                        ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
                        ->count();
                    $class->homeroom_teacher = $class->homeroomTeacher?->teacher?->name ?? '-';
                })
            : collect();

        return view('teacher.classes.index', compact('classes'));
    }

    public function showClass(Request $request, $id)
    {
        $classIds = $this->teacherClassIds();
        abort_unless(in_array((int) $id, $classIds), 403);

        $class = ClassModel::with(['major', 'homeroomTeacher.teacher.user'])
            ->withCount('students')
            ->findOrFail($id);
        $class->schedule_count = Schedule::where('class_id', $class->id)->count();
        $class->homeroom_teacher = $class->homeroomTeacher?->teacher?->name ?? '-';

        $search = $request->input('search');
        $studentsQuery = \App\Models\Student::with('user')
            ->where('class_id', $class->id)
            ->when($search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                ->orWhere('nis', 'like', "%{$search}%"))
            ->orderBy('nis');

        $students = $studentsQuery->paginate(25)->withQueryString();

        // Ringkasan kehadiran per siswa dari DailyAttendance.
        $attendanceMap = DailyAttendance::whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $students->getCollection()->transform(function ($student) use ($attendanceMap) {
            $records = $attendanceMap[$student->id] ?? collect();
            $count = fn ($s) => $records->where('status', $s)->count();
            $student->attendance_summary = [
                'present' => $count('hadir'),
                'excused' => $count('izin'),
                'sick' => $count('sakit'),
                'absent' => $count('alfa'),
            ];

            return $student;
        });

        return view('teacher.classes.show', compact('class', 'students'));
    }

    public function attendance(Request $request)
    {
        $teacher = $this->currentTeacher();
        $selectedDate = $request->input('date', today()->format('Y-m-d'));
        $dayName = Carbon::parse($selectedDate)->format('l');

        // Opsi tanggal: 7 hari ke belakang + 7 hari ke depan.
        $dateOptions = collect(range(-7, 7))->mapWithKeys(function ($offset) {
            $d = today()->addDays($offset);

            return [$d->format('Y-m-d') => $d->translatedFormat('d M Y (l)')];
        })->all();

        $sessions = $teacher
            ? Schedule::with(['class', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('day', $dayName)
                ->orderBy('start_time')
                ->get()
                ->each(function ($session) use ($selectedDate) {
                    $session->setRelation('classroom', $session->class);
                    $studentIds = \App\Models\Student::where('class_id', $session->class_id)->pluck('id');
                    $total = $studentIds->count();
                    $filled = $total > 0
                        ? DailyAttendance::whereIn('student_id', $studentIds)->whereDate('date', $selectedDate)->count()
                        : 0;
                    $session->attendance_completed = $total > 0 && $filled >= $total;
                })
            : collect();

        // Kompatibilitas tombol "Absensi" dari detail kelas (?class=): langsung ke halaman input.
        if ($request->filled('class')) {
            $target = $sessions->firstWhere('class_id', (int) $request->input('class'));
            if ($target) {
                return redirect()->route('teacher.attendance.show', [
                    'sessionId' => $target->id,
                    'date' => $selectedDate,
                ]);
            }
        }

        return view('teacher.attendance.index', compact(
            'sessions', 'dateOptions', 'selectedDate'
        ));
    }

    public function showSession(Request $request, $sessionId)
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $session = $this->buildSessionData((int) $sessionId, $date);
        abort_if(! $session, 403);

        return view('teacher.attendance.session', compact('session'));
    }

    /**
     * Status kehadiran sesi dalam format JSON untuk polling realtime
     * di halaman input absensi guru. Hanya sesi milik guru yang login.
     */
    public function attendanceStatus(Request $request, $sessionId)
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $session = $this->buildSessionData((int) $sessionId, $date);
        abort_if(! $session, 403);

        $summary = ['HADIR' => 0, 'IZIN' => 0, 'SAKIT' => 0, 'ALFA' => 0];
        $students = [];
        foreach ($session->students ?? [] as $student) {
            $status = $student->pivot->status ?? 'ALFA';
            $summary[$status] = ($summary[$status] ?? 0) + 1;
            $students[] = [
                'id' => $student->id,
                'status' => $status,
            ];
        }

        return response()->json([
            'date' => $session->date,
            'students' => $students,
            'summary' => $summary,
            'attendance_completed' => (bool) $session->attendance_completed,
        ]);
    }

    public function updateSession(Request $request, $sessionId)
    {
        $teacher = $this->currentTeacher();
        $schedule = Schedule::with('class')->findOrFail($sessionId);
        abort_if($teacher && (int) $schedule->teacher_id !== (int) $teacher->id, 403);

        $date = $request->input('date', today()->format('Y-m-d'));

        $data = $request->validate([
            'date' => ['nullable', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', 'string'],
            'excuse_file' => ['nullable', 'array'],
            'excuse_file.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'excuse_reason' => ['nullable', 'array'],
            'excuse_reason.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $allowed = ['hadir', 'izin', 'sakit', 'alfa'];
        $studentIds = \App\Models\Student::where('class_id', $schedule->class_id)->pluck('id')->all();
        $studentIdSet = array_flip($studentIds);

        foreach ($data['attendance'] as $studentId => $status) {
            if (! isset($studentIdSet[(int) $studentId])) {
                continue;
            }
            $normalized = strtolower($status);
            if (! in_array($normalized, $allowed, true)) {
                continue;
            }
            DailyAttendance::updateOrCreate(
                ['student_id' => (int) $studentId, 'date' => $date],
                ['status' => $normalized, 'source' => 'teacher']
            );

            // Izin / Sakit wajib disertai surat: simpan sebagai Excuse yang langsung disetujui guru.
            if (in_array($normalized, ['izin', 'sakit'], true)) {
                $uploaded = $request->file("excuse_file.{$studentId}");
                $existing = Excuse::where('student_id', (int) $studentId)->whereDate('date', $date)->first();
                $filePath = $existing?->file_path;
                if ($uploaded) {
                    $filePath = $uploaded->store('excuses', 'public');
                }
                Excuse::updateOrCreate(
                    ['student_id' => (int) $studentId, 'date' => $date],
                    [
                        'type' => $normalized === 'sakit' ? 'Sakit' : 'Izin',
                        'reason' => $data['excuse_reason'][$studentId] ?? $existing?->reason ?? 'Dibuat oleh guru saat input absensi',
                        'file_path' => $filePath,
                        'status' => 'approved',
                        'reviewed_by' => auth()->id(),
                        'reject_reason' => null,
                    ]
                );
            }
        }

        return redirect()
            ->route('teacher.attendance.show', ['sessionId' => $sessionId, 'date' => $date])
            ->with('success', 'Absensi berhasil disimpan.');
    }

    private function buildSessionData(int $scheduleId, string $date): ?object
    {
        $teacher = $this->currentTeacher();
        $schedule = Schedule::with(['class.students.user', 'subject'])
            ->find($scheduleId);

        if (! $schedule) {
            return null;
        }
        if ($teacher && (int) $schedule->teacher_id !== (int) $teacher->id) {
            return null;
        }

        $schedule->setRelation('classroom', $schedule->class);

        $attendances = DailyAttendance::whereIn(
            'student_id',
            $schedule->class->students->pluck('id')
        )->whereDate('date', $date)->get()->keyBy('student_id');

        $excuses = Excuse::whereIn('student_id', $schedule->class->students->pluck('id'))
            ->whereDate('date', $date)->get()->keyBy('student_id');

        $allFilled = $schedule->class->students->count() > 0;
        foreach ($schedule->class->students as $student) {
            $record = $attendances[$student->id] ?? null;
            $excuse = $excuses[$student->id] ?? null;
            if ($record?->status) {
                // Absensi nyata (check-in siswa / input guru sebelumnya).
                $statusLower = strtolower($record->status);
            } elseif ($excuse) {
                // Ada surat izin/sakit untuk tanggal ini.
                $statusLower = match (strtolower($excuse->type)) {
                    'sakit' => 'sakit',
                    default => 'izin',
                };
            } else {
                // Belum absen sama sekali -> Alfa.
                $statusLower = 'alfa';
            }
            $statusUpper = strtoupper($statusLower);
            // Kompatibilitas blade lama yang membaca $student->pivot->status (HADIR/IZIN/...)
            // sekaligus atribut baru $student->attendance_status (lowercase).
            $student->pivot = (object) ['status' => $statusUpper];
            $student->attendance_status = $statusLower;
            $student->excuse_for_date = $excuse;
            if (! $record) {
                $allFilled = false;
            }
        }

        $schedule->students = $schedule->class->students;
        $schedule->attendance_completed = $allFilled && $attendances->count() >= $schedule->class->students->count();
        $schedule->date = $date;

        return $schedule;
    }

    public function showStudent($id)
    {
        $classIds = $this->teacherClassIds();
        $student = \App\Models\Student::with(['user', 'class.major'])->findOrFail($id);
        abort_unless(in_array((int) $student->class_id, $classIds), 403);

        // Alias blade lama.
        $student->setRelation('classroom', $student->class);

        $records = DailyAttendance::where('student_id', $student->id)
            ->orderByDesc('date')
            ->get();

        $count = fn ($s) => $records->where('status', $s)->count();
        $present = $count('hadir');
        $total = $records->count();
        $student->attendance_summary = [
            'present' => $present,
            'excused' => $count('izin'),
            'sick' => $count('sakit'),
            'absent' => $count('alfa'),
            'rate' => $total > 0 ? round($present / $total * 100).'%' : '0%',
        ];

        $student->attendance_history = $records->take(10)->map(function ($r) {
            return (object) [
                'date' => $r->date,
                'status' => strtoupper($r->status),
                'time' => $r->check_in_time,
                'session' => null,
            ];
        });

        $student->excuses = Excuse::where('student_id', $student->id)
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        return view('teacher.students.show', compact('student'));
    }

    public function excuses(Request $request)
    {
        $classIds = $this->teacherClassIds();

        $query = Excuse::with(['student.user', 'student.class'])
            ->whereHas('student', fn ($q) => $q->whereIn('class_id', $classIds ?: [-1]))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->whereHas('student.user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhere('reason', 'like', "%{$search}%");
            })
            ->orderByDesc('date');

        $excuses = $query->paginate(15)->withQueryString();

        // Opsi siswa untuk modal upload.
        $studentOptions = $classIds
            ? \App\Models\Student::with('user')
                ->whereIn('class_id', $classIds)
                ->get()
                ->mapWithKeys(fn ($s) => [$s->id => ($s->user->name ?? $s->nis)])
                ->all()
            : [];

        return view('teacher.excuses.index', compact('excuses', 'studentOptions'));
    }

    public function showExcuse($id)
    {
        $classIds = $this->teacherClassIds();
        $excuse = Excuse::with(['student.user', 'student.class'])->findOrFail($id);
        abort_unless(in_array((int) $excuse->student?->class_id, $classIds), 403);
        $excuse->student?->setRelation('classroom', $excuse->student->class);

        return view('teacher.excuses.show', compact('excuse'));
    }

    public function approveExcuse($id)
    {
        $excuse = $this->scopedExcuse((int) $id);
        $excuse->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reject_reason' => null,
        ]);

        return redirect()->route('teacher.excuses')->with('success', 'Surat izin disetujui.');
    }

    public function rejectExcuse(Request $request, $id)
    {
        $excuse = $this->scopedExcuse((int) $id);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:1000']]);
        $excuse->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reject_reason' => $data['reason'] ?? null,
        ]);

        return redirect()->route('teacher.excuses')->with('success', 'Surat izin ditolak.');
    }

    private function scopedExcuse(int $id): Excuse
    {
        $classIds = $this->teacherClassIds();
        $excuse = Excuse::with('student')->findOrFail($id);
        abort_unless(in_array((int) $excuse->student?->class_id, $classIds), 403);

        return $excuse;
    }

    public function reports(Request $request)
    {
        $classIds = $this->teacherClassIds();

        $classes = $classIds
            ? ClassModel::with('major')->whereIn('id', $classIds)->get()
            : collect();

        $classOptions = $classes->mapWithKeys(fn ($c) => [$c->id => $c->name])->all();

        $reportType = $request->input('type', 'daily');
        $classId = $request->input('class_id');
        $startDate = $request->input('start_date', today()->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->format('Y-m-d'));

        $targetClassIds = $classId ? [(int) $classId] : $classIds;
        if ($classId && ! in_array((int) $classId, $classIds)) {
            abort(403);
        }

        $reportData = collect();
        $reportStats = ['total_students' => 0, 'present' => 0, 'excused' => 0, 'sick' => 0, 'absent' => 0];

        if (! empty($targetClassIds)) {
            $reportClasses = ClassModel::withCount('students')->whereIn('id', $targetClassIds)->get();
            $studentIdsByClass = \App\Models\Student::whereIn('class_id', $targetClassIds)
                ->get(['id', 'class_id'])
                ->groupBy('class_id');

            $attendances = DailyAttendance::whereBetween('date', [$startDate, $endDate])
                ->whereIn('student_id', $studentIdsByClass->flatten()->pluck('id'))
                ->get()
                ->groupBy('student_id');

            foreach ($reportClasses as $class) {
                $ids = ($studentIdsByClass[$class->id] ?? collect())->pluck('id');
                $present = $excused = $sick = $absent = 0;
                foreach ($ids as $sid) {
                    foreach ($attendances[$sid] ?? [] as $record) {
                        match (strtolower($record->status)) {
                            'hadir' => $present++,
                            'izin' => $excused++,
                            'sakit' => $sick++,
                            default => $absent++,
                        };
                    }
                }
                // Siswa tanpa catatan pada rentang dianggap alfa 1x.
                $recordedStudentIds = $attendances->keys()->intersect($ids);
                $absent += $ids->count() - $recordedStudentIds->count();

                $reportData->push([
                    'class_name' => $class->name,
                    'total_students' => $class->students_count,
                    'present' => $present,
                    'excused' => $excused,
                    'sick' => $sick,
                    'absent' => $absent,
                ]);

                $reportStats['total_students'] += $class->students_count;
                $reportStats['present'] += $present;
                $reportStats['excused'] += $excused;
                $reportStats['sick'] += $sick;
                $reportStats['absent'] += $absent;
            }

            $total = max(1, $reportStats['present'] + $reportStats['excused'] + $reportStats['sick'] + $reportStats['absent']);
            $reportStats['present_rate'] = round($reportStats['present'] / $total * 100).'%';
            $reportStats['absent_rate'] = round($reportStats['absent'] / $total * 100).'%';
        }

        return view('teacher.reports.index', compact(
            'reportData', 'reportStats', 'reportType', 'classOptions', 'classId', 'startDate', 'endDate'
        ));
    }
}

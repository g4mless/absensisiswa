<?php

namespace App\Http\Controllers;

use App\Events\PklLocationUpdated;
use App\Models\DailyAttendance;
use App\Models\PklLocationLog;
use App\Models\StudentPkl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentPklController extends Controller
{
    /**
     * Authorization backend (PRD pasal 5 & 17): hanya role siswa_pkl,
     * hanya data milik sendiri, dan harus siswa PKL (students.is_pkl
     * true ATAU punya record student_pkl berstatus ACTIVE).
     *
     * @return array{student: \App\Models\Student, pklData: StudentPkl}
     */
    protected function authorizePkl(): array
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'siswa_pkl') {
            abort(403, 'Akses ditolak.');
        }

        $student = $user->student;
        if (! $student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        $pklData = StudentPkl::with('pembimbing.user')
            ->where('student_id', $student->id)
            ->active()
            ->latest('id')
            ->first();

        if (! $student->is_pkl && ! $pklData) {
            abort(403, 'Akun ini tidak terdaftar sebagai siswa PKL.');
        }

        // student_pkl.student_id unik & pkl_location_logs.student_pkl_id
        // NOT NULL: pastikan selalu ada satu record agar GPS/checkin jalan
        // meski admin baru menandai is_pkl tanpa membuat record PKL.
        if (! $pklData) {
            $pklData = StudentPkl::firstOrCreate(
                ['student_id' => $student->id],
                ['tempat_pkl' => '-', 'status' => 'ACTIVE'],
            );
            $pklData->loadMissing('pembimbing.user');
        }

        return compact('student', 'pklData');
    }

    public function dashboard()
    {
        ['student' => $student, 'pklData' => $pklData] = $this->authorizePkl();

        $attendances = DailyAttendance::where('student_id', $student->id);
        $todayAttendance = (clone $attendances)->whereDate('date', today())->first();

        $todayLog = PklLocationLog::where('student_pkl_id', $pklData->id)
            ->whereDate('recorded_at', today())
            ->latest('recorded_at')
            ->first();

        // GPS dianggap aktif jika ada log hari ini dalam 30 menit terakhir.
        $gpsActive = $todayLog && $todayLog->recorded_at->greaterThan(now()->subMinutes(30));
        $lastGpsUpdate = $todayLog?->recorded_at;

        $totalPklDays = (clone $attendances)->count();
        $totalHadir = (clone $attendances)->where('status', 'hadir')->count();
        $totalTidakHadir = $totalPklDays - $totalHadir;
        $attendancePercentage = $totalPklDays > 0
            ? round($totalHadir / $totalPklDays * 100).'%'
            : '0%';

        return view('student-pkl.dashboard', compact(
            'pklData', 'todayAttendance', 'gpsActive', 'lastGpsUpdate',
            'totalPklDays', 'totalHadir', 'totalTidakHadir', 'attendancePercentage'
        ));
    }

    public function attendance()
    {
        ['student' => $student] = $this->authorizePkl();

        $todayAttendance = DailyAttendance::where('student_id', $student->id)
            ->whereDate('date', today())
            ->first();

        return view('student-pkl.attendance.index', compact('todayAttendance'));
    }

    public function checkin(Request $request)
    {
        ['student' => $student, 'pklData' => $pklData] = $this->authorizePkl();

        // Validasi GPS server-side; jangan percaya perhitungan frontend.
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'between:0,100000'],
        ]);

        $now = now();

        // Jika record PKL punya rentang tanggal, tolak checkin di luar periode.
        $today = $now->toDateString();
        if (($pklData->start_date && $today < $pklData->start_date->toDateString())
            || ($pklData->end_date && $today > $pklData->end_date->toDateString())) {
            return response()->json([
                'message' => 'Hari ini di luar periode PKL ('.
                    ($pklData->start_date?->format('d/m/Y') ?? '-').
                    ' - '.
                    ($pklData->end_date?->format('d/m/Y') ?? '-').').',
            ], 422);
        }

        if (DailyAttendance::where('student_id', $student->id)->whereDate('date', today())->exists()) {
            return response()->json(['message' => 'Anda sudah melakukan absensi hari ini.'], 422);
        }

        // PKL tidak wajib radius sekolah: simpan koordinat apa adanya.
        DB::transaction(function () use ($student, $data, $now) {
            DailyAttendance::create([
                'student_id' => $student->id,
                'date' => $now->toDateString(),
                'status' => 'hadir',
                'check_in_time' => $now->format('H:i:s'),
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'accuracy' => $data['accuracy'] ?? null,
                'source' => 'pkl',
            ]);
        });

        return response()->json(['message' => 'Check in PKL berhasil!']);
    }

    public function checkout(Request $request)
    {
        ['student' => $student] = $this->authorizePkl();

        $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'between:0,100000'],
        ]);

        // Validasi milik sendiri: hanya absensi milik student login hari ini.
        $attendance = DailyAttendance::where('student_id', $student->id)
            ->whereDate('date', today())
            ->first();

        if (! $attendance) {
            return response()->json(['message' => 'Belum check in hari ini.'], 422);
        }
        if ($attendance->check_out_time) {
            return response()->json(['message' => 'Anda sudah check out hari ini.'], 422);
        }

        $attendance->update(['check_out_time' => now()->format('H:i:s')]);

        return response()->json(['message' => 'Check out PKL berhasil!']);
    }

    public function location()
    {
        ['pklData' => $pklData] = $this->authorizePkl();

        $locationHistory = PklLocationLog::where('student_pkl_id', $pklData->id)
            ->whereDate('recorded_at', today())
            ->orderByDesc('recorded_at')
            ->limit(50)
            ->get();

        return view('student-pkl.location', compact('locationHistory'));
    }

    public function sendLocation(Request $request)
    {
        ['pklData' => $pklData] = $this->authorizePkl();

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'between:0,100000'],
        ]);

        $now = now();

        // Throttling: tolak jika log terakhir < 10 detik yang lalu.
        $lastLog = PklLocationLog::where('student_pkl_id', $pklData->id)
            ->latest('recorded_at')
            ->first();
        if ($lastLog && $lastLog->recorded_at->diffInSeconds($now) < 10) {
            return response()->json([
                'message' => 'Terlalu sering mengirim lokasi. Coba lagi beberapa detik.',
            ], 429);
        }

        $log = PklLocationLog::create([
            'student_pkl_id' => $pklData->id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'accuracy' => $data['accuracy'] ?? null,
            'recorded_at' => $now, // server timestamp
        ]);

        // Broadcast realtime ke channel pkl.student.{id} (PRD pasal 13).
        // Aman bila BROADCAST_CONNECTION=log/null: hanya ditulis ke log, tidak error.
        broadcast(new PklLocationUpdated(
            studentPklId: (int) $pklData->id,
            studentId: (int) $pklData->student_id,
            latitude: $log->latitude,
            longitude: $log->longitude,
            accuracy: $log->accuracy,
            recordedAt: $log->recorded_at->toDateTimeString(),
        ));

        return response()->json([
            'message' => 'Lokasi berhasil dikirim!',
            'recorded_at' => $log->recorded_at->toDateTimeString(),
        ]);
    }

    public function history(Request $request)
    {
        ['student' => $student, 'pklData' => $pklData] = $this->authorizePkl();

        $attendanceQuery = DailyAttendance::where('student_id', $student->id)
            ->orderByDesc('date');
        if ($request->filled('start_date')) {
            $attendanceQuery->whereDate('date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $attendanceQuery->whereDate('date', '<=', $request->input('end_date'));
        }
        $attendances = $attendanceQuery->paginate(15, ['*'], 'attendance_page')->withQueryString();

        $locations = PklLocationLog::where('student_pkl_id', $pklData->id)
            ->orderByDesc('recorded_at')
            ->paginate(15, ['*'], 'location_page')->withQueryString();

        return view('student-pkl.history', compact('attendances', 'locations'));
    }

    public function profile()
    {
        ['student' => $student, 'pklData' => $pklData] = $this->authorizePkl();
        $student->loadMissing(['user', 'class']);

        return view('student-pkl.profile', compact('student', 'pklData'));
    }

    public function updateProfile(Request $request)
    {
        ['student' => $student] = $this->authorizePkl();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $student->user->update(['name' => $data['name']]);
        $student->update(collect($data)->only(['phone', 'address'])->all());

        return redirect()->route('student-pkl.profile')->with('success', 'Profil berhasil diperbarui.');
    }
}

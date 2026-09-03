<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendar;
use App\Models\AttendanceSetting;
use App\Models\DailyAttendance;
use App\Models\SchoolLocation;
use App\Services\Attendance\LocationRandomnessService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = auth()->user()->student;
        $attendances = DailyAttendance::where('student_id', $student?->id);
        $todayAttendance = (clone $attendances)->whereDate('date', today())->first();
        $recentAttendance = (clone $attendances)->orderByDesc('date')->limit(5)->get();
        $weekAttendance = (clone $attendances)->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $monthAttendance = (clone $attendances)->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $totalAttendance = (clone $attendances)->count();

        return view('student.dashboard', compact(
            'todayAttendance', 'recentAttendance', 'weekAttendance', 'monthAttendance', 'totalAttendance'
        ));
    }

    public function attendance()
    {
        $student = auth()->user()->student;
        $todayAttendance = $student
            ? DailyAttendance::where('student_id', $student->id)->whereDate('date', today())->first()
            : null;
        $attendanceSetting = AttendanceSetting::first() ?? new AttendanceSetting([
            'start_time' => '06:00:00',
            'end_time' => '07:30:00',
        ]);
        $today = now();
        $calendar = AcademicCalendar::with('holidays')
            ->whereDate('month', $today->copy()->startOfMonth())
            ->first();
        $attendanceAvailable = $calendar
            && $today->isWeekday()
            && ! $calendar->isHoliday($today)
            && ! $calendar->isLocked($today);
        $attendanceMessage = ! $calendar
            ? 'Kalender akademik bulan ini belum diperbarui.'
            : (! $today->isWeekday()
                ? 'Absensi hanya dibuka pada hari Senin sampai Jumat.'
                : ($calendar->isHoliday($today)
                    ? 'Hari ini merupakan hari libur akademik.'
                    : ($calendar->isLocked($today) ? 'Absensi bulan ini sudah ditutup permanen.' : null)));

        return view('student.attendance.index', compact('todayAttendance', 'attendanceSetting', 'attendanceAvailable', 'attendanceMessage'));
    }

    public function history()
    {
        $student = auth()->user()->student;
        $query = DailyAttendance::where('student_id', $student?->id)
            ->orderByDesc('date');

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->input('end_date'));
        }

        $attendances = $query->paginate(15)->withQueryString();

        return view('student.attendance.history', compact('attendances'));
    }

    public function checkin(Request $request, LocationRandomnessService $randomness)
    {
        $data = $request->validate([
            'latitude' => ['nullable', 'required_without:samples', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_without:samples', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'samples' => ['nullable', 'array', 'min:1', 'max:10'],
            'samples.*.latitude' => ['required_with:samples', 'numeric', 'between:-90,90'],
            'samples.*.longitude' => ['required_with:samples', 'numeric', 'between:-180,180'],
            'samples.*.accuracy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'samples.*.timestamp' => ['nullable', 'integer', 'min:0'],
            'samples.*.speed' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'samples.*.heading' => ['nullable', 'numeric', 'min:0', 'max:360'],
            'samples.*.altitude' => ['nullable', 'numeric', 'min:-1000', 'max:10000'],
            'samples.*.altitude_accuracy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        $student = auth()->user()->student;
        if (! $student) {
            return response()->json(['message' => 'Data siswa tidak ditemukan.'], 422);
        }

        $now = now();
        if (! $now->isWeekday()) {
            return response()->json(['message' => 'Absensi hanya dibuka pada hari Senin sampai Jumat.'], 422);
        }

        $calendar = AcademicCalendar::with('holidays')
            ->whereDate('month', $now->copy()->startOfMonth())
            ->first();
        if (! $calendar) {
            return response()->json(['message' => 'Kalender akademik bulan ini belum diperbarui.'], 422);
        }
        if ($calendar->isHoliday($now)) {
            return response()->json(['message' => 'Hari ini merupakan hari libur akademik.'], 422);
        }
        if ($calendar->isLocked($now)) {
            return response()->json(['message' => 'Absensi bulan ini sudah ditutup permanen.'], 422);
        }

        if (DailyAttendance::where('student_id', $student->id)->whereDate('date', today())->exists()) {
            return response()->json(['message' => 'Anda sudah melakukan absensi hari ini.'], 422);
        }

        $setting = AttendanceSetting::first();
        $start = Carbon::today()->setTimeFromTimeString($setting?->start_time ?? '06:00:00');
        $end = Carbon::today()->setTimeFromTimeString($setting?->end_time ?? '07:30:00');
        if (! $now->between($start, $end)) {
            return response()->json([
                'message' => 'Absensi hanya dibuka pukul '.$start->format('H:i').' sampai '.$end->format('H:i').'.',
            ], 422);
        }

        $location = SchoolLocation::first();
        if (! $location) {
            return response()->json(['message' => 'Lokasi sekolah belum dikonfigurasi.'], 422);
        }

        // --- GPS movement randomness detection (multi-sinyal + skor) ---
        // Seluruh sample dianalisis 6 sinyal independen (static, linear,
        // uniform_steps, accuracy, timing, sensor). Skor >= reject ditolak,
        // skor >= flag lolos dengan peringatan. Satu sinyal tidak pernah
        // cukup untuk penolakan (indikasi, bukan kepastian tunggal).
        $samples = $data['samples'] ?? [];
        if ($samples === [] && isset($data['latitude'], $data['longitude'])) {
            $samples = [[
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'accuracy' => $data['accuracy'] ?? null,
                'timestamp' => now()->valueOf(),
            ]];
        }

        $analysis = $randomness->analyze($samples);

        if (($analysis['risk_action'] ?? 'allow') === 'reject') {
            return response()->json([
                'message' => 'Check-in ditolak: pola lokasi terdeteksi sebagai Fake GPS (skor risiko '.$analysis['risk_score'].'/100). Matikan aplikasi Fake GPS / mock location, aktifkan GPS akurasi tinggi, tunggu beberapa detik, lalu coba lagi.',
                'location_analysis' => $analysis,
            ], 422);
        }

        $representative = $randomness->representativeCoordinate($samples)
            ?? ['latitude' => (float) ($data['latitude'] ?? 0), 'longitude' => (float) ($data['longitude'] ?? 0), 'accuracy' => $data['accuracy'] ?? null];

        $distance = $this->distanceInMeters(
            (float) $representative['latitude'],
            (float) $representative['longitude'],
            (float) $location->latitude,
            (float) $location->longitude,
        );
        if ($distance > $location->radius) {
            return response()->json(['message' => 'Anda berada di luar radius sekolah.'], 422);
        }

        DailyAttendance::create([
            'student_id' => $student->id,
            'date' => today(),
            'status' => 'hadir',
            'check_in_time' => $now->format('H:i:s'),
            'latitude' => $representative['latitude'],
            'longitude' => $representative['longitude'],
            'accuracy' => $representative['accuracy'],
            'source' => 'web',
            'gps_samples' => array_values($samples),
            'sample_count' => $analysis['sample_count'],
            'unique_coordinates' => $analysis['unique_coordinates'],
            'duplicate_ratio' => $analysis['duplicate_ratio'],
            'max_spread_meters' => $analysis['max_spread_meters'],
            'risk_score' => $analysis['risk_score'],
            'is_location_suspicious' => $analysis['is_suspicious'],
            'location_flags' => $analysis['flags'],
        ]);

        return response()->json([
            'message' => 'Check in berhasil!',
            'location_analysis' => $analysis,
            'warning' => $analysis['is_suspicious']
                ? 'Pola lokasi mencurigakan (skor risiko '.$analysis['risk_score'].'/100, sinyal: '.implode(', ', array_keys($analysis['signals'])).'). Data tetap disimpan untuk verifikasi.'
                : null,
        ]);
    }

    public function profile()
    {
        $student = auth()->user()->student;

        return view('student.profile', compact('student'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        auth()->user()->update(['name' => $data['name']]);
        auth()->user()->student()->update(collect($data)->only(['phone', 'address'])->all());

        return redirect()->route('student.profile');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        auth()->user()->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('student.profile')->with('success', 'Password berhasil diubah.');
    }

    private function distanceInMeters(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
    {
        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($latitude2 - $latitude1);
        $longitudeDelta = deg2rad($longitude2 - $longitude1);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($longitudeDelta / 2) ** 2;

        return $earthRadius * 2 * asin(sqrt($a));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use App\Models\SchoolLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function dashboard()
    {
        $upcomingSchedule = collect();
        $recentAttendance = collect();
        return view('student.dashboard', compact('upcomingSchedule', 'recentAttendance'));
    }

    public function attendance()
    {
        $student = auth()->user()->student;
        $todayAttendance = $student
            ? DailyAttendance::where('student_id', $student->id)->whereDate('date', today())->first()
            : null;

        return view('student.attendance.index', compact('todayAttendance'));
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

    public function checkin(Request $request)
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $student = auth()->user()->student;
        if (!$student) {
            return response()->json(['message' => 'Data siswa tidak ditemukan.'], 422);
        }

        if (DailyAttendance::where('student_id', $student->id)->whereDate('date', today())->exists()) {
            return response()->json(['message' => 'Anda sudah melakukan absensi hari ini.'], 422);
        }

        $now = now();
        $start = Carbon::today()->setTime(6, 0);
        $end = Carbon::today()->setTime(7, 30);
        if (!$now->between($start, $end)) {
            return response()->json(['message' => 'Absensi hanya dibuka pukul 06:00 sampai 07:30.'], 422);
        }

        $location = SchoolLocation::first();
        if (!$location) {
            return response()->json(['message' => 'Lokasi sekolah belum dikonfigurasi.'], 422);
        }

        $distance = $this->distanceInMeters(
            (float) $data['latitude'],
            (float) $data['longitude'],
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
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'source' => 'web',
        ]);

        return response()->json(['message' => 'Check in berhasil!']);
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
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        auth()->user()->update(collect($data)->only(['name', 'email'])->all());
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

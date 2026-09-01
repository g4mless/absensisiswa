<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentPklController extends Controller
{
    public function dashboard()
    {
        return view('student-pkl.dashboard');
    }

    public function attendance()
    {
        return view('student-pkl.attendance.index');
    }

    public function checkin(Request $request)
    {
        return redirect()->route('student-pkl.attendance');
    }

    public function checkout(Request $request)
    {
        return redirect()->route('student-pkl.attendance');
    }

    public function location()
    {
        $locationHistory = collect();
        return view('student-pkl.location', compact('locationHistory'));
    }

    public function sendLocation(Request $request)
    {
        return redirect()->route('student-pkl.location');
    }

    public function history()
    {
        $attendances = collect();
        $locations = collect();
        return view('student-pkl.history', compact('attendances', 'locations'));
    }

    public function profile()
    {
        $item = auth()->user();
        return view('student-pkl.profile', compact('item'));
    }

    public function updateProfile(Request $request)
    {
        return redirect()->route('student-pkl.profile');
    }
}

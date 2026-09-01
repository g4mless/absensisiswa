<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSetting;
use Illuminate\Http\Request;

class AttendanceSettingController extends Controller
{
    public function index()
    {
        $setting = AttendanceSetting::first() ?? new AttendanceSetting([
            'start_time' => '06:00:00',
            'end_time' => '07:30:00',
        ]);

        return view('admin.attendance-setting.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        AttendanceSetting::updateOrCreate(['id' => 1], $data);

        return redirect()->route('admin.attendance-setting.index')
            ->with('success', 'Rentang waktu absensi berhasil disimpan.');
    }
}

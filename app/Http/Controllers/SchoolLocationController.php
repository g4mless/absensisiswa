<?php

namespace App\Http\Controllers;

use App\Models\SchoolLocation;
use Illuminate\Http\Request;

class SchoolLocationController extends Controller
{
    public function index()
    {
        $location = SchoolLocation::first();
        return view('admin.school-location.index', compact('location'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:1'],
            'school_name' => ['required', 'string', 'max:255'],
        ]);
        $location = SchoolLocation::first();
        if ($location) {
            $location->update($data);
        } else {
            SchoolLocation::create($data);
        }

        return redirect()->route('admin.school-location.index');
    }
}

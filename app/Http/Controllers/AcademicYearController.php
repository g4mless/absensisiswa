<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('year')->paginate(15);
        return view('admin.academic-years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('admin.academic-years.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data) {
            if (! empty($data['is_active'])) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
            }
            AcademicYear::create($data + ['is_active' => ! empty($data['is_active'])]);
        });

        return redirect()->route('admin.academic-years.index');
    }

    public function show($id)
    {
        $item = AcademicYear::findOrFail($id);
        return view('admin.academic-years.show', compact('item'));
    }

    public function edit($id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        return view('admin.academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, $id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        $data = $request->validate([
            'year' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($academicYear, $data) {
            if (! empty($data['is_active'])) {
                AcademicYear::where('id', '!=', $academicYear->id)->where('is_active', true)->update(['is_active' => false]);
            }
            $academicYear->update($data + ['is_active' => ! empty($data['is_active'])]);
        });

        return redirect()->route('admin.academic-years.index');
    }

    public function destroy($id)
    {
        AcademicYear::findOrFail($id)->delete();

        return redirect()->route('admin.academic-years.index');
    }
}

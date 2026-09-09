<?php

namespace App\Http\Controllers;

use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MajorController extends Controller
{
    public function index()
    {
        $majors = Major::orderBy('name')->paginate(15);
        return view('admin.majors.index', compact('majors'));
    }

    public function create()
    {
        return view('admin.majors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:majors,code'],
        ]);

        $major = Major::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($major, 201);
        }

        if ($redirect = $request->input('redirect')) {
            return redirect($redirect)->with('success', 'Jurusan berhasil dibuat.');
        }

        return redirect()->route('admin.majors.index')->with('success', 'Jurusan berhasil dibuat.');
    }

    public function show($id)
    {
        $item = Major::findOrFail($id);
        return view('admin.majors.show', compact('item'));
    }

    public function edit($id)
    {
        $major = Major::findOrFail($id);
        return view('admin.majors.edit', compact('major'));
    }

    public function update(Request $request, $id)
    {
        $major = Major::findOrFail($id);
        $major->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('majors', 'code')->ignore($major->id)],
        ]));
        return redirect()->route('admin.majors.index');
    }

    public function destroy($id)
    {
        Major::findOrFail($id)->delete();
        return redirect()->route('admin.majors.index');
    }
}

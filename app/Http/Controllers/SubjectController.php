<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::orderBy('name')->paginate(15);
        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        Subject::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]));
        return redirect()->route('admin.subjects.index');
    }

    public function show($id)
    {
        $item = Subject::findOrFail($id);
        return view('admin.subjects.show', compact('item'));
    }

    public function edit($id)
    {
        $subject = Subject::findOrFail($id);
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $subject->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]));
        return redirect()->route('admin.subjects.index');
    }

    public function destroy($id)
    {
        Subject::findOrFail($id)->delete();
        return redirect()->route('admin.subjects.index');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $count = Subject::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.subjects.index')->with('status', $count . ' mata pelajaran berhasil dihapus.');
    }

    public function allDestroy()
    {
        $count = Subject::query()->delete();
        return redirect()->route('admin.subjects.index')->with('status', $count . ' mata pelajaran berhasil dihapus.');
    }
}

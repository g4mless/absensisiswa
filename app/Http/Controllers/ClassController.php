<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassModel::with('major');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('grade', 'like', "%{$search}%")
                    ->orWhere('section', 'like', "%{$search}%")
                    ->orWhereHas('major', fn ($major) => $major
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"));
            });
        }

        $classes = $query->orderByRaw("FIELD(grade, 'X', 'XI', 'XII')")
            ->orderBy('major_id')
            ->orderBy('section')
            ->paginate(15)
            ->withQueryString();
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        return view('admin.classes.create', ['majors' => Major::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'major_id' => ['required', 'exists:majors,id'],
            'grade' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'section' => ['required', 'string', 'max:20'],
        ]);

        ClassModel::create($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil dibuat.');
    }

    public function show($id)
    {
        $item = ClassModel::findOrFail($id);
        return view('admin.classes.show', compact('item'));
    }

    public function edit($id)
    {
        $class = ClassModel::findOrFail($id);
        return view('admin.classes.edit', [
            'class' => $class,
            'majors' => Major::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $class = ClassModel::findOrFail($id);
        $validated = $request->validate([
            'major_id' => ['required', 'exists:majors,id'],
            'grade' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'section' => ['required', 'string', 'max:20'],
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        ClassModel::findOrFail($id)->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $count = ClassModel::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.classes.index')->with('success', $count . ' kelas berhasil dihapus.');
    }

    public function allDestroy()
    {
        $count = ClassModel::query()->delete();
        return redirect()->route('admin.classes.index')->with('success', $count . ' kelas berhasil dihapus.');
    }
}

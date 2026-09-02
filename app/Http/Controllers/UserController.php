<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa', 'siswa_pkl', 'teacher', 'student'])],
        ]);
        $data['role'] = ['teacher' => 'guru', 'student' => 'siswa'][$data['role']] ?? $data['role'];
        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('admin.users.index');
    }

    public function show($id)
    {
        $item = User::findOrFail($id);
        return view('admin.users.show', compact('item'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa', 'siswa_pkl', 'teacher', 'student'])],
        ]);
        $data['role'] = ['teacher' => 'guru', 'student' => 'siswa'][$data['role']] ?? $data['role'];
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('admin.users.index');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect()->route('admin.users.index');
    }
}

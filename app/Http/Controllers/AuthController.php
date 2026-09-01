<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectByRole(Auth::user());
        }

        // Siswa masuk menggunakan nama lengkap dan NIS sebagai password.
        $student = User::whereIn('role', ['siswa', 'siswa_pkl'])
            ->where('is_active', true)
            ->whereHas('student', fn ($query) => $query->where('is_active', true))
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($credentials['username']))])
            ->first();

        if ($student && Hash::check($credentials['password'], $student->password)) {
            Auth::login($student, $request->boolean('remember'));
            $request->session()->regenerate();

            return $this->redirectByRole($student);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectByRole($user)
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('teacher.dashboard'),
            'siswa' => redirect()->route('student.dashboard'),
            'siswa_pkl' => redirect()->route('student-pkl.dashboard'),
            default => redirect()->route('login'),
        };
    }
}

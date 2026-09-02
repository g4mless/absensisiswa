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
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if (! str_starts_with($user->password, '$2y$')) {
                $user->update(['password' => Hash::make($user->password)]);
            }

            return $this->redirectByRole($user);
        }

        // Fallback: cek plaintext password (untuk data impor belum di-hash)
        $user = User::where('username', $credentials['username'])->first();
        if ($user && ! str_starts_with($user->password, '$2y$') && $credentials['password'] === $user->password) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $user->update(['password' => Hash::make($user->password)]);

            return $this->redirectByRole($user);
        }

        // Siswa masuk menggunakan nama lengkap dan NIS sebagai password.
        $student = User::whereIn('role', ['siswa', 'siswa_pkl'])
            ->whereHas('student')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($credentials['username']))])
            ->first();

        if ($student) {
            $passwordMatch = str_starts_with($student->password, '$2y$')
                ? Hash::check($credentials['password'], $student->password)
                : $credentials['password'] === $student->password;

            if ($passwordMatch) {
                Auth::login($student, $request->boolean('remember'));
                $request->session()->regenerate();

                if (! str_starts_with($student->password, '$2y$')) {
                    $student->update(['password' => Hash::make($student->password)]);
                }

                return $this->redirectByRole($student);
            }
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

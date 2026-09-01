@extends('layouts.guest')

@section('content')
    <div class="p-6 md:p-8">
        <div class="mb-6 text-center md:hidden">
            <h2 class="text-lg font-semibold text-gray-900">Masuk ke Akun Anda</h2>
            <p class="text-sm text-gray-500 mt-1">Masukkan kredensial untuk melanjutkan</p>
        </div>

        <div class="hidden md:block mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Masuk ke Akun Anda</h2>
            <p class="text-sm text-gray-500 mt-1">Masukkan kredensial untuk melanjutkan</p>
        </div>

        @if(session('status'))
            <x-alert variant="success" :title="session('status')" class="mb-6" />
        @endif

        @if($errors->any())
            <x-alert variant="danger" title="Terjadi Kesalahan" class="mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="space-y-4">
                <x-input
                    label="Username / Nama Lengkap Siswa"
                    name="username"
                    type="text"
                    value="{{ old('username') }}"
                    required
                    autofocus
                    placeholder="Username atau nama lengkap siswa"
                />

                <x-input
                    label="Password"
                    name="password"
                    type="password"
                    required
                    placeholder="Password atau NIS siswa"
                />
            </div>

            <div class="flex items-center justify-between mt-6 mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="remember"
                        class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <span class="text-sm text-gray-600">Ingat saya</span>
                </label>
            </div>

            <x-button variant="primary" type="submit" class="w-full justify-center">
                Masuk
            </x-button>
        </form>
    </div>
@endsection

@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Pengguna</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Buat Pengguna Baru</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <x-input label="Nama Lengkap" name="name" :error="$errors->first('name')" value="{{ old('name') }}" />
                <x-input label="Username" name="username" :error="$errors->first('username')" value="{{ old('username') }}" />
                <x-input label="Password" name="password" type="password" :error="$errors->first('password')" />
                <x-input label="Konfirmasi Password" name="password_confirmation" type="password" />
                <x-select label="Peran" name="role" :options="['admin' => 'Admin', 'teacher' => 'Guru', 'student' => 'Siswa']" placeholder="Pilih Peran" :error="$errors->first('role')" />
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Buat Pengguna</x-button>
                <a href="{{ route('admin.users.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

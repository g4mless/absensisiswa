@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="mx-auto max-w-2xl space-y-4 sm:space-y-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">Profile</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola informasi profil Anda</p>
    </div>

    <x-card>
        <form method="POST" action="{{ route('teacher.profile') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <x-input label="Nama" name="name" value="{{ auth()->user()->name }}" required />
                <x-input label="Username" name="username" value="{{ auth()->user()->username }}" disabled />
                <x-input label="Role" name="role" value="{{ auth()->user()->role }}" disabled />
            </div>
            <div class="mt-6">
                <x-button variant="primary" type="submit" class="min-h-[44px] w-full sm:w-auto">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-card>

    <x-card>
        <x-slot name="header">Ubah Password</x-slot>
        <form method="POST" action="{{ route('teacher.profile') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <x-input label="Password Saat Ini" name="current_password" type="password" required />
                <x-input label="Password Baru" name="password" type="password" required />
                <x-input label="Konfirmasi Password Baru" name="password_confirmation" type="password" required />
            </div>
            <div class="mt-6">
                <x-button variant="primary" type="submit" class="min-h-[44px] w-full sm:w-auto">Ubah Password</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Profile</h1>
        <p class="text-gray-500">Kelola informasi profil Anda</p>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.profile') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <x-input label="Nama" name="name" value="{{ auth()->user()->name }}" required />
                <x-input label="Username" name="username" value="{{ auth()->user()->username }}" disabled />
                <x-input label="Role" name="role" value="{{ auth()->user()->role }}" disabled />
            </div>
            <div class="mt-6">
                <x-button variant="primary" type="submit">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-card>

    <x-card>
        <x-slot name="header">Ubah Password</x-slot>
        <form method="POST" action="{{ route('admin.profile') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <x-input label="Password Saat Ini" name="current_password" type="password" required />
                <x-input label="Password Baru" name="password" type="password" required />
                <x-input label="Konfirmasi Password Baru" name="password_confirmation" type="password" required />
            </div>
            <div class="mt-6">
                <x-button variant="primary" type="submit">Ubah Password</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

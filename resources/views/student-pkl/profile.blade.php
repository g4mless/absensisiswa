@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-student-pkl')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
        <p class="text-gray-500">Kelola informasi profil PKL Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Card --}}
        <div class="lg:col-span-1">
            <x-card elevated>
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-primary-100 text-3xl font-bold text-primary-700">
                        {{ substr(Auth::user()->name ?? 'P', 0, 1) }}
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $student->user->name ?? '-' }}</h3>
                    <p class="text-sm text-gray-500">{{ $student->nis ?? '-' }}</p>

                    <div class="mt-6 w-full space-y-3 text-left">
                        <div class="flex justify-between border-b border-gray-100 py-2">
                            <span class="text-sm text-gray-500">Kelas</span>
                            <span class="text-sm font-medium text-gray-900">{{ $student->class->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 py-2">
                            <span class="text-sm text-gray-500">NIS</span>
                            <span class="text-sm font-medium text-gray-900">{{ $student->nis ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 py-2">
                            <span class="text-sm text-gray-500">Email</span>
                            <span class="text-sm font-medium text-gray-900">{{ $student->user->email ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 py-2">
                            <span class="text-sm text-gray-500">Telepon</span>
                            <span class="text-sm font-medium text-gray-900">{{ $student->phone ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- PKL Info Card --}}
            <x-card class="mt-6">
                <x-slot name="header">Info PKL</x-slot>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">Perusahaan</span>
                        <span class="text-sm font-medium text-gray-900">{{ $pklData->company ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">Pembimbing</span>
                        <span class="text-sm font-medium text-gray-900">{{ $pklData->supervisor ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">Tanggal Mulai</span>
                        <span class="text-sm font-medium text-gray-900">{{ $pklData->start_date ? \Carbon\Carbon::parse($pklData->start_date)->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-500">Tanggal Selesai</span>
                        <span class="text-sm font-medium text-gray-900">{{ $pklData->end_date ? \Carbon\Carbon::parse($pklData->end_date)->format('d/m/Y') : '-' }}</span>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Edit Profile --}}
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <x-slot name="header">Edit Profil</x-slot>
                <x-slot name="subtitle">Perbarui informasi profil Anda</x-slot>

                @if($errors->any())
                    <div class="mb-4">
                        <x-alert variant="danger" title="Terjadi Kesalahan" dismissible>
                            <ul class="mt-1 list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    </div>
                @endif

                <form method="POST" action="{{ route('student-pkl.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input label="Nama Lengkap" name="name" :value="old('name', $student->user->name ?? '')" required />
                        <x-input label="Email" name="email" type="email" :value="old('email', $student->user->email ?? '')" required />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input label="Telepon" name="phone" type="tel" :value="old('phone', $student->phone ?? '')" />
                        <x-input label="NIS" name="nis" :value="old('nis', $student->nis ?? '')" disabled />
                    </div>

                    <x-input label="Alamat" name="address" :value="old('address', $student->address ?? '')" />

                    <div class="flex justify-end">
                        <x-button type="submit" variant="primary">Simpan Perubahan</x-button>
                    </div>
                </form>
            </x-card>

            {{-- Change Password --}}
            <x-card>
                <x-slot name="header">Ubah Password</x-slot>
                <x-slot name="subtitle">Pastikan menggunakan password yang kuat</x-slot>

                <form method="POST" action="{{ route('student-pkl.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <x-input label="Password Saat Ini" name="current_password" type="password" required />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input label="Password Baru" name="password" type="password" required />
                        <x-input label="Konfirmasi Password" name="password_confirmation" type="password" required />
                    </div>

                    <div class="flex justify-end">
                        <x-button type="submit" variant="secondary">Ubah Password</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</div>
@endsection

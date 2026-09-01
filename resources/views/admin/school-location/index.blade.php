@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Lokasi Sekolah</h1>
        <p class="text-gray-500">Konfigurasi lokasi absensi berbasis GPS</p>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif

    <x-card>
        <form action="{{ route('admin.school-location.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input label="Latitude" name="latitude" :error="$errors->first('latitude')" value="{{ old('latitude', $location->latitude ?? '') }}" placeholder="-6.2088" />
                    <x-input label="Longitude" name="longitude" :error="$errors->first('longitude')" value="{{ old('longitude', $location->longitude ?? '') }}" placeholder="106.8456" />
                </div>
                <x-input label="Radius (meter)" name="radius" type="number" :error="$errors->first('radius')" value="{{ old('radius', $location->radius ?? 100) }}" placeholder="100" />
                <x-input label="Nama Sekolah" name="school_name" :error="$errors->first('school_name')" value="{{ old('school_name', $location->school_name ?? '') }}" placeholder="SMK Negeri 1" />

                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Pratinjau Peta</h3>
                    <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <p class="mt-2 text-sm">Integrasi peta dapat ditambahkan di sini</p>
                            <p class="text-xs text-gray-400">Current: {{ $location->latitude ?? '-' }}, {{ $location->longitude ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Simpan Pengaturan</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

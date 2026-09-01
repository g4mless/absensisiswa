@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-student-pkl')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Selamat Datang, {{ Auth::user()->name ?? 'Siswa PKL' }}</h1>
        <p class="text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- PKL Status Card --}}
    <x-card elevated>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Status PKL</p>
                <div class="mt-1 flex items-center gap-3">
                    @if($pklData->status ?? null)
                        <x-badge variant="success">AKTIF</x-badge>
                    @else
                        <x-badge variant="neutral">TIDAK AKTIF</x-badge>
                    @endif
                </div>
                @if($pklData->company ?? null)
                    <p class="mt-2 text-sm text-gray-600">
                        <span class="font-medium">{{ $pklData->company }}</span>
                        &middot; Pembimbing: {{ $pklData->supervisor ?? '-' }}
                    </p>
                @endif
            </div>
            @if(!($todayAttendance ?? null))
                <a href="{{ route('student-pkl.attendance') }}">
                    <x-button variant="primary" size="lg">Check In PKL</x-button>
                </a>
            @endif
        </div>
    </x-card>

    {{-- Today's Attendance --}}
    <x-card>
        <x-slot name="header">Absensi Hari Ini</x-slot>
        <div class="flex items-center gap-4">
            @if($todayAttendance ?? null)
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">
                        Check-in: {{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i') }}
                    </p>
                    <x-badge variant="success" class="mt-1">{{ strtoupper($todayAttendance->status) }}</x-badge>
                </div>
            @else
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Belum Check In</p>
                    <p class="text-xs text-gray-500">Silakan check in di halaman absensi</p>
                </div>
            @endif
        </div>
    </x-card>

    {{-- GPS Tracking Status --}}
    <x-card>
        <x-slot name="header">Status GPS Tracking</x-slot>
        <div class="flex items-center gap-3">
            <div class="flex h-3 w-3">
                <span class="absolute inline-flex h-3 w-3 animate-ping rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex h-3 w-3 rounded-full bg-green-500"></span>
            </div>
            <p class="text-sm text-gray-600">
                @if($gpsActive ?? false)
                    GPS aktif &middot; Lokasi terkirim terakhir: {{ $lastGpsUpdate ? \Carbon\Carbon::parse($lastGpsUpdate)->diffForHumans() : '-' }}
                @else
                    GPS belum aktif. Aktifkan di halaman Lokasi GPS.
                @endif
            </p>
        </div>
    </x-card>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Hari PKL" value="{{ $totalPklDays ?? 0 }}" icon="calendar" description="hari total" />
        <x-stat-card title="Hadir" value="{{ $totalHadir ?? 0 }}" icon="check-circle" description="hari" />
        <x-stat-card title="Tidak Hadir" value="{{ $totalTidakHadir ?? 0 }}" icon="x-circle" description="hari" />
        <x-stat-card title="Persentase" value="{{ $attendancePercentage ?? '0%' }}" icon="chart-bar" />
    </div>
</div>
@endsection

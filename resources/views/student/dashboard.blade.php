@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-student')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Selamat Datang, {{ Auth::user()->name ?? 'Siswa' }}</h1>
        <p class="text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Today's Attendance Status --}}
    <x-card elevated>
        <div class="flex flex-col items-center gap-4 py-4 sm:flex-row sm:justify-between">
            <div class="text-center sm:text-left">
                <p class="text-sm font-medium text-gray-500">Status Absensi Hari Ini</p>
                @if($todayAttendance ?? null)
                    <div class="mt-2 flex items-center gap-3">
                        <x-badge variant="{{ $todayAttendance->status === 'hadir' ? 'success' : ($todayAttendance->status === 'izin' ? 'info' : ($todayAttendance->status === 'sakit' ? 'warning' : 'danger')) }}">
                            {{ strtoupper($todayAttendance->status) }}
                        </x-badge>
                        <span class="text-sm text-gray-600">
                            Check-in: {{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i') }}
                        </span>
                    </div>
                @else
                    <p class="mt-2 text-lg font-semibold text-gray-400">Belum Absen</p>
                @endif
            </div>

            @if(!($todayAttendance ?? null))
                <a href="{{ route('student.attendance') }}">
                    <x-button variant="primary" size="lg">Check In Sekarang</x-button>
                </a>
            @endif
        </div>
    </x-card>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Minggu Ini" value="{{ $weekAttendance ?? 0 }}" icon="calendar" description="hari hadir" />
        <x-stat-card title="Bulan Ini" value="{{ $monthAttendance ?? 0 }}" icon="calendar-days" description="hari hadir" />
        <x-stat-card title="Total Kehadiran" value="{{ $totalAttendance ?? 0 }}" icon="check-circle" description="hari" />
        <x-stat-card title="Persentase" value="{{ $attendancePercentage ?? '0%' }}" icon="chart-bar" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Upcoming Schedule --}}
        <x-card>
            <x-slot name="header">Jadwal Mendatang</x-slot>
            <x-slot name="subtitle">Jadwal pelajaran hari ini</x-slot>

            <div class="space-y-3">
                @forelse($upcomingSchedule ?? [] as $schedule)
                    <div class="flex items-center gap-4 rounded-lg border border-gray-100 p-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-sm font-bold text-primary-700">
                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $schedule->subject->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $schedule->teacher->name ?? '-' }} &middot; {{ $schedule->room ?? '-' }}</p>
                        </div>
                        <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</span>
                    </div>
                @empty
                    <x-empty-state title="Tidak ada jadwal" description="Belum ada jadwal pelajaran untuk hari ini." />
                @endforelse
            </div>
        </x-card>

        {{-- Recent Attendance --}}
        <x-card>
            <x-slot name="header">Riwayat Terakhir</x-slot>
            <x-slot name="subtitle">5 riwayat kehadiran terakhir</x-slot>

            <div class="space-y-3">
                @forelse($recentAttendance ?? [] as $record)
                    <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($record->date)->translatedFormat('d M Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</p>
                        </div>
                        <x-badge variant="{{ $record->status === 'hadir' ? 'success' : ($record->status === 'izin' ? 'info' : ($record->status === 'sakit' ? 'warning' : 'danger')) }}">
                            {{ strtoupper($record->status) }}
                        </x-badge>
                    </div>
                @empty
                    <x-empty-state title="Belum ada riwayat" description="Belum ada data kehadiran." />
                @endforelse
            </div>

            @if(($recentAttendance ?? collect())->isNotEmpty())
                <div class="mt-4">
                    <a href="{{ route('student.history') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                        Lihat Semua Riwayat &rarr;
                    </a>
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection

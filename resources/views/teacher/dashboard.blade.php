@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-500">Selamat datang, {{ auth()->user()->name }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Kelas Saya" value="{{ $totalClasses ?? 0 }}" icon="users" />
        <x-stat-card title="Sesi Hari Ini" value="{{ $todaySessions ?? 0 }}" icon="calendar" />
        <x-stat-card title="Siswa Hadir" value="{{ $studentsPresentToday ?? 0 }}" icon="check-circle" trend="up" trendValue="{{ $attendanceRate ?? '0%' }}" />
        <x-stat-card title="Surat Izin Pending" value="{{ $pendingExcuses ?? 0 }}" icon="document" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">Jadwal Hari Ini</x-slot>
                <x-slot name="subtitle">{{ now()->translatedFormat('l, d F Y') }}</x-slot>

                <div class="space-y-3">
                    @forelse($todaySchedule ?? [] as $session)
                        <div class="flex items-center gap-4 rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-sm font-bold text-primary-700">
                                {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $session->subject->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $session->classroom->name ?? '-' }} &middot; {{ $session->room ?? '-' }}</p>
                            </div>
                            <x-badge variant="{{ $session->is_current ? 'success' : 'neutral' }}">
                                {{ $session->is_current ? 'Berlangsung' : \Carbon\Carbon::parse($session->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                            </x-badge>
                        </div>
                    @empty
                        <x-empty-state title="Tidak ada jadwal hari ini" description="Anda tidak memiliki sesi mengajar hari ini." />
                    @endforelse
                </div>
            </x-card>
        </div>

        <div>
            <x-card>
                <x-slot name="header">Aksi Cepat</x-slot>
                <div class="space-y-3">
                    <a href="{{ route('teacher.attendance') }}" class="block">
                        <x-button variant="primary" class="w-full">Input Absensi</x-button>
                    </a>
                    <a href="{{ route('teacher.excuses') }}" class="block">
                        <x-button variant="warning" class="w-full">Lihat Surat Izin</x-button>
                    </a>
                    <a href="{{ route('teacher.reports') }}" class="block">
                        <x-button variant="secondary" class="w-full">Buat Laporan</x-button>
                    </a>
                    <a href="{{ route('teacher.classes') }}" class="block">
                        <x-button variant="ghost" class="w-full">Lihat Kelas</x-button>
                    </a>
                </div>
            </x-card>

            @if(isset($recentExcuses) && count($recentExcuses) > 0)
            <x-card class="mt-6">
                <x-slot name="header">Surat Izin Terbaru</x-slot>
                <div class="space-y-3">
                    @foreach($recentExcuses as $excuse)
                        <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $excuse->student->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $excuse->date ? \Carbon\Carbon::parse($excuse->date)->format('d M Y') : '-' }}</p>
                            </div>
                            <x-badge variant="{{ $excuse->status === 'pending' ? 'warning' : ($excuse->status === 'approved' ? 'success' : 'danger') }}">
                                {{ ucfirst($excuse->status) }}
                            </x-badge>
                        </div>
                    @endforeach
                </div>
            </x-card>
            @endif
        </div>
    </div>
</div>
@endsection

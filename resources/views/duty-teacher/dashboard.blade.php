@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-duty-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Guru Piket</h1>
        <p class="text-gray-500">Ringkasan kehadiran siswa hari ini</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Tidak Hadir Hari Ini" value="{{ $notPresentToday ?? 0 }}" icon="x-circle" description="Siswa yang tidak hadir" />
        <x-stat-card title="Dengan Surat Izin" value="{{ $withExcuse ?? 0 }}" icon="document" description="Siswa berizin" />
        <x-stat-card title="Belum Absen" value="{{ $noAttendanceYet ?? 0 }}" icon="question-mark-circle" description="Siswa belum tercatat" />
        <x-stat-card title="Total Siswa" value="{{ $totalStudents ?? 0 }}" icon="users" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">Masalah Kehadiran Hari Ini</x-slot>
                <x-slot name="subtitle">{{ now()->translatedFormat('l, d F Y') }}</x-slot>

                <div class="space-y-3">
                    @forelse($attendanceIssues ?? [] as $issue)
                        <div class="flex items-center gap-4 rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $issue->student->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $issue->student->classroom->name ?? '-' }} &middot; {{ $issue->session->subject->name ?? '-' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-badge variant="{{ match($issue->status) { 'ALFA' => 'danger', 'IZIN' => 'warning', 'SAKIT' => 'info', default => 'neutral' } }}">
                                    {{ $issue->status }}
                                </x-badge>
                                <x-dropdown>
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z"/></svg>
                                        </button>
                                    </x-slot>
                                    <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                        Hubungi Orang Tua
                                    </a>
                                    <a href="{{ route('duty-teacher.attendance.today') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        Lihat Detail
                                    </a>
                                </x-dropdown>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="Tidak ada masalah" description="Semua siswa hadir hari ini. Tidak ada masalah kehadiran." />
                    @endforelse
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card>
                <x-slot name="header">Ringkasan Hari Ini</x-slot>
                <div class="space-y-3">
                    @php
                        $summary = [
                            ['label' => 'Total Siswa', 'value' => $totalStudents ?? 0, 'color' => 'gray'],
                            ['label' => 'Hadir', 'value' => $presentToday ?? 0, 'color' => 'green'],
                            ['label' => 'Izin', 'value' => $excusedToday ?? 0, 'color' => 'yellow'],
                            ['label' => 'Sakit', 'value' => $sickToday ?? 0, 'color' => 'blue'],
                            ['label' => 'Alfa', 'value' => $absentToday ?? 0, 'color' => 'red'],
                        ];
                    @endphp
                    @foreach($summary as $item)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-{{ $item['color'] }}-500"></span>
                                <span class="text-sm text-gray-600">{{ $item['label'] }}</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card>
                <x-slot name="header">Aksi Cepat</x-slot>
                <div class="space-y-3">
                    <a href="{{ route('duty-teacher.attendance.today') }}" class="block">
                        <x-button variant="primary" class="w-full">Absensi Hari Ini</x-button>
                    </a>
                    <a href="{{ route('duty-teacher.attendance.all') }}" class="block">
                        <x-button variant="secondary" class="w-full">Semua Absensi</x-button>
                    </a>
                    <a href="{{ route('duty-teacher.reports.semester') }}" class="block">
                        <x-button variant="ghost" class="w-full">Rekap Semester</x-button>
                    </a>
                </div>
            </x-card>

            @if(isset($recentNotices) && count($recentNotices) > 0)
            <x-card>
                <x-slot name="header">Pengumuman</x-slot>
                <div class="space-y-2">
                    @foreach($recentNotices as $notice)
                        <div class="rounded-lg border border-gray-100 p-3">
                            <p class="text-xs text-gray-500">{{ $notice->date ? \Carbon\Carbon::parse($notice->date)->format('d M') : '' }}</p>
                            <p class="text-sm text-gray-700 mt-1">{{ Str::limit($notice->content, 80) }}</p>
                        </div>
                    @endforeach
                </div>
            </x-card>
            @endif
        </div>
    </div>
</div>
@endsection

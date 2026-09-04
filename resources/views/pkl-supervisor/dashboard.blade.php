@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-pkl-supervisor')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Pembimbing PKL</h1>
        <p class="text-gray-500">Selamat datang, {{ Auth::user()->name ?? 'Pembimbing' }}</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Total Siswa" value="{{ $totalStudents ?? 0 }}" icon="users" description="siswa PKL" />
        <x-stat-card title="PKL Aktif" value="{{ $activePkl ?? 0 }}" icon="briefcase" description="sedang PKL" />
        <x-stat-card title="Absensi Hari Ini" value="{{ $todayAttendance ?? 0 }}" icon="check-circle" description="sudah absen" />
        <x-stat-card title="Masalah" value="{{ $issues ?? 0 }}" icon="alert-triangle" description="perlu ditindak" trend="{{ $issues > 0 ? 'up' : null }}" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Assigned Students --}}
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">Siswa PKL Saya</x-slot>
                <x-slot name="subtitle">Daftar siswa yang menjadi bimbingan Anda</x-slot>

                <x-table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Perusahaan</th>
                            <th>Status</th>
                            <th>GPS Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedStudents ?? [] as $student)
                            <tr>
                                <td>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $student->user->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $student->nis ?? '-' }}</p>
                                    </div>
                                </td>
                                <td class="text-sm text-gray-600">{{ $student->pkl->company ?? '-' }}</td>
                                <td>
                                    @if($student->todayAttendance ?? null)
                                        <x-badge variant="success">HADIR</x-badge>
                                    @else
                                        <x-badge variant="warning">BELUM ABSEN</x-badge>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-500">
                                    {{ $student->lastGpsTime ? \Carbon\Carbon::parse($student->lastGpsTime)->diffForHumans() : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="Tidak ada siswa" description="Belum ada siswa PKL yang ditugaskan." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>

                @if(($assignedStudents ?? collect())->count() > 0)
                    <div class="mt-4">
                        <a href="{{ route('pkl-supervisor.students.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                            Lihat Semua Siswa &rarr;
                        </a>
                    </div>
                @endif
            </x-card>
        </div>

        {{-- Recent GPS Alerts --}}
        <div>
            <x-card>
                <x-slot name="header">Peringatan GPS</x-slot>
                <x-slot name="subtitle">Alert terbaru</x-slot>

                <div class="space-y-3">
                    @forelse($gpsAlerts ?? [] as $alert)
                        <div class="flex items-start gap-3 rounded-lg border border-gray-100 p-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100">
                                <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $alert->student->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $alert->message }}</p>
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($alert->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="Tidak ada peringatan" description="Semua siswa dalam kondisi aman." />
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

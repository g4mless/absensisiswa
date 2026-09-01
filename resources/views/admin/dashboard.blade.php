@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Total Siswa" value="{{ $totalStudents ?? 0 }}" icon="users" trend="up" trendValue="+12%" />
        <x-stat-card title="Total Guru" value="{{ $totalTeachers ?? 0 }}" icon="user-tie" trend="up" trendValue="+3%" />
        <x-stat-card title="Absensi Hari Ini" value="{{ $todayAttendance ?? '0%' }}" icon="clipboard-check" trend="up" trendValue="+5%" />
        <x-stat-card title="Kelas Aktif" value="{{ $activeClasses ?? 0 }}" icon="school" trend="down" trendValue="-1%" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">Absensi Terkini</x-slot>
                <div class="overflow-x-auto">
                    <x-table>
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendance ?? [] as $record)
                                <tr>
                                    <td>{{ $record->student->name ?? '-' }}</td>
                                    <td>{{ $record->student->class->name ?? '-' }}</td>
                                    <td>
                                        <x-badge variant="{{ $record->status === 'present' ? 'success' : ($record->status === 'late' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($record->status) }}
                                        </x-badge>
                                    </td>
                                    <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-gray-400 py-4">Tidak ada catatan absensi hari ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-table>
                </div>
            </x-card>
        </div>

        <div>
            <x-card>
                <x-slot name="header">Aksi Cepat</x-slot>
                <div class="space-y-3">
                    <a href="{{ route('admin.students.create') }}" class="block">
                        <x-button variant="primary" class="w-full">Tambah Siswa Baru</x-button>
                    </a>
                    <a href="{{ route('admin.teachers.create') }}" class="block">
                        <x-button variant="secondary" class="w-full">Tambah Guru Baru</x-button>
                    </a>
                    <a href="{{ route('admin.attendance.index') }}" class="block">
                        <x-button variant="success" class="w-full">Lihat Absensi</x-button>
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="block">
                        <x-button variant="ghost" class="w-full">Buat Laporan</x-button>
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

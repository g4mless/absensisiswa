@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Laporan Absensi</h1>
        <p class="text-gray-500">Buat dan unduh laporan kehadiran siswa</p>
    </div>

    <x-card>
        <x-slot name="header">Filter Laporan</x-slot>

        <form method="GET" action="{{ route('teacher.reports.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-select label="Jenis Laporan" name="type" :options="['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan']" :value="$reportType ?? 'daily'" />
                <x-input label="Tanggal Mulai" name="start_date" type="date" :value="$startDate ?? date('Y-m-d')" />
                <x-input label="Tanggal Akhir" name="end_date" type="date" :value="$endDate ?? date('Y-m-d')" />
                <x-select label="Kelas" name="class_id" :options="$classOptions ?? []" placeholder="Semua Kelas" :value="$classId ?? ''" />
            </div>

            <div class="flex gap-2">
                <x-button type="submit" variant="primary">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    Tampilkan
                </x-button>
                @if(isset($reportData))
                    <a href="{{ route('teacher.reports.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
                        <x-button variant="secondary" size="md">
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            CSV
                        </x-button>
                    </a>
                    <a href="{{ route('teacher.reports.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}">
                        <x-button variant="secondary" size="md">
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            XLSX
                        </x-button>
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    @if(isset($reportData))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card title="Total Siswa" value="{{ $reportStats['total_students'] ?? 0 }}" icon="users" />
            <x-stat-card title="Hadir" value="{{ $reportStats['present'] ?? 0 }}" icon="check-circle" description="{{ $reportStats['present_rate'] ?? '0%' }}" />
            <x-stat-card title="Izin & Sakit" value="{{ ($reportStats['excused'] ?? 0) + ($reportStats['sick'] ?? 0) }}" icon="document" />
            <x-stat-card title="Alfa" value="{{ $reportStats['absent'] ?? 0 }}" icon="x-circle" description="{{ $reportStats['absent_rate'] ?? '0%' }}" />
        </div>

        <x-card>
            <x-slot name="header">Hasil Laporan</x-slot>
            <x-slot name="subtitle">{{ $reportType ?? 'Harian' }} &middot; {{ $startDate ?? date('Y-m-d') }} s/d {{ $endDate ?? date('Y-m-d') }}</x-slot>

            <div class="overflow-x-auto">
                <x-table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kelas</th>
                            <th class="text-center">Total Siswa</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Sakit</th>
                            <th class="text-center">Alfa</th>
                            <th class="text-center">Tingkat Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $index => $row)
                            <tr>
                                <td class="text-gray-500">{{ $index + 1 }}</td>
                                <td class="font-medium">{{ $row['class_name'] ?? '-' }}</td>
                                <td class="text-center">{{ $row['total_students'] ?? 0 }}</td>
                                <td class="text-center">
                                    <span class="text-sm font-semibold text-green-600">{{ $row['present'] ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm font-semibold text-yellow-600">{{ $row['excused'] ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm font-semibold text-blue-600">{{ $row['sick'] ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-sm font-semibold text-red-600">{{ $row['absent'] ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $rate = $row['total_students'] > 0 ? round(($row['present'] / $row['total_students']) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="h-2 w-16 rounded-full bg-gray-200 overflow-hidden">
                                            <div class="h-full rounded-full {{ $rate >= 80 ? 'bg-green-500' : ($rate >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $rate }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold {{ $rate >= 80 ? 'text-green-600' : ($rate >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $rate }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-empty-state title="Tidak ada data" description="Tidak ditemukan data untuk filter yang dipilih." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>
            </div>
        </x-card>

        <x-card>
            <x-slot name="header">Grafik Kehadiran</x-slot>
            <div class="h-64 flex items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    <p class="mt-2 text-sm text-gray-500">Grafik kehadiran akan ditampilkan di sini</p>
                </div>
            </div>
        </x-card>
    @endif
</div>
@endsection

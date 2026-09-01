@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Laporan</h1>
        <p class="text-gray-500">Buat dan ekspor laporan absensi</p>
    </div>

    <x-card>
        <form method="GET" action="{{ route('admin.reports.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <x-input label="Tanggal Mulai" name="start_date" type="date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}" />
                <x-input label="Tanggal Akhir" name="end_date" type="date" value="{{ request('end_date', now()->format('Y-m-d')) }}" />
                <x-select label="Kelas" name="class_id" :options="$classes->pluck('name', 'id')->toArray()" placeholder="Semua Kelas" value="{{ request('class_id') }}" />
            </div>
            <div class="flex items-center gap-3">
                <x-button variant="primary" type="submit">Buat Laporan</x-button>
                @if(isset($reportData))
                    <a href="{{ route('admin.reports.export', ['format' => 'csv'] + request()->only(['start_date', 'end_date', 'class_id'])) }}">
                        <x-button variant="success">Ekspor CSV</x-button>
                    </a>
                    <a href="{{ route('admin.reports.export', ['format' => 'xlsx'] + request()->only(['start_date', 'end_date', 'class_id'])) }}">
                        <x-button variant="secondary">Ekspor XLSX</x-button>
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    @if(isset($reportData))
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <x-stat-card title="Total Catatan" value="{{ $reportData['total'] ?? 0 }}" icon="clipboard-list" />
            <x-stat-card title="Hadir" value="{{ $reportData['present'] ?? 0 }}" icon="check-circle" trend="up" trendValue="{{ $reportData['present_rate'] ?? '0%' }}" />
            <x-stat-card title="Terlambat" value="{{ $reportData['late'] ?? 0 }}" icon="clock" trend="down" trendValue="{{ $reportData['late_rate'] ?? '0%' }}" />
            <x-stat-card title="Tidak Hadir" value="{{ $reportData['absent'] ?? 0 }}" icon="x-circle" trend="down" trendValue="{{ $reportData['absent_rate'] ?? '0%' }}" />
        </div>

        <x-card>
            <x-slot name="header">Ringkasan Absensi</x-slot>
            <div class="overflow-x-auto">
                <x-table>
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Hadir</th>
                            <th>Terlambat</th>
                            <th>Tidak Hadir</th>
                            <th>Tingkat Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData['students'] ?? [] as $studentReport)
                            <tr>
                                <td class="font-medium">{{ $studentReport['name'] }}</td>
                                <td>{{ $studentReport['class'] }}</td>
                                <td>
                                    <x-badge variant="success">{{ $studentReport['present'] }}</x-badge>
                                </td>
                                <td>
                                    <x-badge variant="warning">{{ $studentReport['late'] }}</x-badge>
                                </td>
                                <td>
                                    <x-badge variant="danger">{{ $studentReport['absent'] }}</x-badge>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $studentReport['rate'] }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ $studentReport['rate'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state title="Tidak ada data tersedia" description="Buat laporan untuk melihat data absensi." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>
            </div>
        </x-card>
    @else
        <x-card>
            <x-empty-state title="Tidak ada laporan dibuat" description="Pilih rentang tanggal dan kelas untuk membuat laporan absensi." />
        </x-card>
    @endif
</div>
@endsection

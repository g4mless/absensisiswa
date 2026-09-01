@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-duty-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Rekap Semester</h1>
        <p class="text-gray-500">Laporan kehadiran siswa per semester</p>
    </div>

    <x-card>
        <x-slot name="header">Filter Laporan</x-slot>

        <form method="GET" action="{{ route('duty-teacher.reports.semester') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-input label="Tanggal Mulai" name="start_date" type="date" :value="$startDate ?? ''" />
                <x-input label="Tanggal Akhir" name="end_date" type="date" :value="$endDate ?? ''" />
                <x-select label="Kelas" name="class_id" :options="$classOptions ?? []" placeholder="Semua Kelas" :value="$classId ?? ''" />
                <div class="flex items-end">
                    <x-button type="submit" variant="primary" class="w-full">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        Tampilkan
                    </x-button>
                </div>
            </div>

            @if(isset($semesterData))
                <div class="flex gap-2">
                    <a href="{{ route('duty-teacher.reports.semester', array_merge(request()->query(), ['export' => 'csv'])) }}">
                        <x-button variant="secondary" size="sm">
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Export CSV
                        </x-button>
                    </a>
                    <a href="{{ route('duty-teacher.reports.semester', array_merge(request()->query(), ['export' => 'xlsx'])) }}">
                        <x-button variant="secondary" size="sm">
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Export XLSX
                        </x-button>
                    </a>
                </div>
            @endif
        </form>
    </x-card>

    @if(isset($semesterData))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card title="Total Siswa" value="{{ $semesterStats['total_students'] ?? 0 }}" icon="users" />
            <x-stat-card title="Rata-rata Kehadiran" value="{{ $semesterStats['avg_attendance'] ?? '0%' }}" icon="chart-bar" description="{{ $semesterStats['avg_rate'] ?? '0%' }}" />
            <x-stat-card title="Total Hari Efektif" value="{{ $semesterStats['effective_days'] ?? 0 }}" icon="calendar" />
            <x-stat-card title="Siswa Bermasalah" value="{{ $semesterStats['problem_students'] ?? 0 }}" icon="warning" description="< 75% kehadiran" />
        </div>

        <x-card>
            <x-slot name="header">Rekap Per Kelas</x-slot>
            <x-slot name="subtitle">{{ $startDate ?? '-' }} s/d {{ $endDate ?? '-' }}</x-slot>

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
                    @forelse($semesterData as $index => $row)
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
                                    $rate = $row['total_students'] > 0 ? round(($row['present'] / ($row['total_students'] * ($row['effective_days'] ?? 1))) * 100, 1) : 0;
                                @endphp
                                <div class="flex items-center justify-center gap-2">
                                    <div class="h-2 w-20 rounded-full bg-gray-200 overflow-hidden">
                                        <div class="h-full rounded-full {{ $rate >= 80 ? 'bg-green-500' : ($rate >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ min($rate, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold min-w-[3rem] text-right {{ $rate >= 80 ? 'text-green-600' : ($rate >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $rate }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state title="Tidak ada data" description="Tidak ditemukan data untuk periode yang dipilih." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>

        @if(isset($problemStudents) && count($problemStudents) > 0)
        <x-card>
            <x-slot name="header">Siswa Bermasalah</x-slot>
            <x-slot name="subtitle">Siswa dengan tingkat kehadiran di bawah 75%</x-slot>

            <x-table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Alfa</th>
                        <th class="text-center">Tingkat Kehadiran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($problemStudents as $index => $student)
                        <tr>
                            <td class="text-gray-500">{{ $index + 1 }}</td>
                            <td class="font-mono text-sm">{{ $student['nis'] ?? '-' }}</td>
                            <td class="font-medium text-sm">{{ $student['name'] ?? '-' }}</td>
                            <td class="text-sm text-gray-600">{{ $student['class_name'] ?? '-' }}</td>
                            <td class="text-center text-sm font-semibold text-green-600">{{ $student['present'] ?? 0 }}</td>
                            <td class="text-center text-sm font-semibold text-red-600">{{ $student['absent'] ?? 0 }}</td>
                            <td class="text-center">
                                <span class="text-sm font-bold text-red-600">{{ $student['rate'] ?? '0%' }}</span>
                            </td>
                            <td class="text-center">
                                <button class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-primary-600 transition-colors" title="Hubungi Orang Tua">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
        </x-card>
        @endif
    @endif
</div>
@endsection

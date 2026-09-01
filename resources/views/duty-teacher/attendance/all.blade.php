@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-duty-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Semua Absensi</h1>
            <p class="text-gray-500">Lihat seluruh data kehadiran siswa</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('duty-teacher.attendance.all', array_merge(request()->query(), ['export' => 'csv'])) }}">
                <x-button variant="secondary" size="sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    CSV
                </x-button>
            </a>
            <a href="{{ route('duty-teacher.attendance.all', array_merge(request()->query(), ['export' => 'xlsx'])) }}">
                <x-button variant="secondary" size="sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    XLSX
                </x-button>
            </a>
        </div>
    </div>

    <x-card>
        <form method="GET" action="{{ route('duty-teacher.attendance.all') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-input label="Tanggal" name="date" type="date" :value="$selectedDate ?? date('Y-m-d')" />
                <x-select label="Kelas" name="class_id" :options="$classOptions ?? []" placeholder="Semua Kelas" :value="$classId ?? ''" />
                <x-select label="Status" name="status" :options="['' => 'Semua', 'HADIR' => 'Hadir', 'IZIN' => 'Izin', 'SAKIT' => 'Sakit', 'ALFA' => 'Alfa']" :value="$status ?? ''" />
                <div class="flex items-end">
                    <x-button type="submit" variant="primary" class="w-full">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        Filter
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>

    @if(isset($attendanceData))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <x-stat-card title="Total" value="{{ $stats['total'] ?? 0 }}" icon="users" />
            <x-stat-card title="Hadir" value="{{ $stats['present'] ?? 0 }}" icon="check-circle" description="{{ $stats['present_rate'] ?? '0%' }}" />
            <x-stat-card title="Izin" value="{{ $stats['excused'] ?? 0 }}" icon="document" />
            <x-stat-card title="Sakit" value="{{ $stats['sick'] ?? 0 }}" icon="medical-bag" />
            <x-stat-card title="Alfa" value="{{ $stats['absent'] ?? 0 }}" icon="x-circle" description="{{ $stats['absent_rate'] ?? '0%' }}" />
        </div>

        <x-card>
            <x-slot name="header">Data Kehadiran</x-slot>
            <x-slot name="subtitle">{{ \Carbon\Carbon::parse($selectedDate ?? date('Y-m-d'))->format('d F Y') }}</x-slot>

            <x-table>
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Sesi</th>
                        <th>Waktu</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendanceData as $index => $record)
                        <tr>
                            <td class="text-gray-500">{{ $index + 1 }}</td>
                            <td class="font-mono text-sm">{{ $record->student->nis ?? '-' }}</td>
                            <td class="font-medium text-sm">{{ $record->student->name ?? '-' }}</td>
                            <td class="text-sm text-gray-600">{{ $record->student->classroom->name ?? '-' }}</td>
                            <td class="text-sm text-gray-600">{{ $record->session->subject->name ?? '-' }}</td>
                            <td class="text-sm text-gray-500">{{ $record->time ?? '-' }}</td>
                            <td class="text-center">
                                @if($record->status)
                                    <x-badge variant="{{ match($record->status) { 'HADIR' => 'success', 'IZIN' => 'warning', 'SAKIT' => 'info', 'ALFA' => 'danger', default => 'neutral' } }}">
                                        {{ $record->status }}
                                    </x-badge>
                                @else
                                    <x-badge variant="neutral">-</x-badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state title="Tidak ada data" description="Tidak ditemukan data kehadiran untuk tanggal ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>

            @if(isset($attendanceData) && $attendanceData instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-4">
                    <x-pagination :paginator="$attendanceData" />
                </div>
            @endif
        </x-card>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-pkl-supervisor')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Absensi PKL</h1>
        <p class="text-gray-500">Overview kehadiran siswa PKL</p>
    </div>

    {{-- Filters --}}
    <x-card>
        <form method="GET" action="{{ route('pkl-supervisor.attendance.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <x-input label="Dari Tanggal" name="start_date" type="date" :value="request('start_date')" />
            </div>
            <div class="flex-1">
                <x-input label="Sampai Tanggal" name="end_date" type="date" :value="request('end_date')" />
            </div>
            <div class="flex-1">
                <x-select label="Siswa" name="student_id" :options="$studentOptions ?? []" placeholder="Semua Siswa" :value="request('student_id')" />
            </div>
            <div class="flex gap-2">
                <x-button type="submit" variant="primary">Filter</x-button>
                @if(request('start_date') || request('end_date') || request('student_id'))
                    <a href="{{ route('pkl-supervisor.attendance.index') }}">
                        <x-button variant="ghost">Reset</x-button>
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    {{-- Attendance Table --}}
    <x-card>
        <x-slot name="header">Data Absensi</x-slot>
        <x-slot name="subtitle">{{ $attendances->total() ?? 0 }} data ditemukan</x-slot>

        <x-table>
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Tanggal</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances ?? [] as $record)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700">
                                    {{ substr($record->student->user->name ?? 'S', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $record->student->user->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $record->student->nis ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm font-medium">{{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}</td>
                        <td class="text-sm">{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                        <td class="text-sm">{{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}</td>
                        <td>
                            <x-badge variant="{{ $record->status === 'hadir' ? 'success' : ($record->status === 'izin' ? 'info' : ($record->status === 'sakit' ? 'warning' : 'danger')) }}">
                                {{ strtoupper($record->status) }}
                            </x-badge>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-empty-state title="Tidak ada data" description="Belum ada data absensi untuk filter yang dipilih." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        @if(($attendances ?? collect())->hasPages())
            <div class="mt-4">
                <x-pagination :paginator="$attendances" />
            </div>
        @endif
    </x-card>
</div>
@endsection

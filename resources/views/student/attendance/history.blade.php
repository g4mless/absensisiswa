@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-student')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Riwayat Kehadiran</h1>
        <p class="text-gray-500">Lihat semua riwayat absensi Anda</p>
    </div>

    {{-- Date Filter --}}
    <x-card>
        <form method="GET" action="{{ route('student.history') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <x-input label="Dari Tanggal" name="start_date" type="date" :value="request('start_date')" />
            </div>
            <div class="flex-1">
                <x-input label="Sampai Tanggal" name="end_date" type="date" :value="request('end_date')" />
            </div>
            <div class="flex gap-2">
                <x-button type="submit" variant="primary">Filter</x-button>
                @if(request('start_date') || request('end_date'))
                    <a href="{{ route('student.history') }}">
                        <x-button variant="ghost">Reset</x-button>
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    {{-- Attendance Table --}}
    <x-card>
        <x-slot name="header">Riwayat Absensi</x-slot>
        <x-slot name="subtitle">{{ $attendances->total() }} data ditemukan</x-slot>

        <x-table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Status</th>
                    <th>Jam Masuk</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $record)
                    <tr>
                        <td class="font-medium">{{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->date)->translatedFormat('l') }}</td>
                        <td>
                            <x-badge variant="{{ $record->status === 'hadir' ? 'success' : ($record->status === 'izin' ? 'info' : ($record->status === 'sakit' ? 'warning' : 'danger')) }}">
                                {{ strtoupper($record->status) }}
                            </x-badge>
                        </td>
                        <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-empty-state title="Tidak ada data" description="Belum ada riwayat kehadiran untuk periode ini." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        @if($attendances->hasPages())
            <div class="mt-4">
                <x-pagination :paginator="$attendances" />
            </div>
        @endif
    </x-card>
</div>
@endsection

@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-pkl-supervisor')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('pkl-supervisor.students.index') }}" class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $student->user->name ?? '-' }}</h1>
            <p class="text-gray-500">Detail Siswa PKL</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile --}}
        <div class="lg:col-span-1 space-y-6">
            <x-card elevated>
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary-100 text-2xl font-bold text-primary-700">
                        {{ substr($student->user->name ?? 'S', 0, 1) }}
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $student->user->name ?? '-' }}</h3>
                    <p class="text-sm text-gray-500">{{ $student->nis ?? '-' }}</p>
                </div>

                <div class="mt-6 space-y-3">
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">Kelas</span>
                        <span class="text-sm font-medium text-gray-900">{{ $student->class->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-500">Telepon</span>
                        <span class="text-sm font-medium text-gray-900">{{ $student->phone ?? '-' }}</span>
                    </div>
                </div>
            </x-card>

            {{-- PKL Details --}}
            <x-card>
                <x-slot name="header">Detail PKL</x-slot>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">Perusahaan</span>
                        <span class="text-sm font-medium text-gray-900">{{ $student->pkl->company ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">Pembimbing</span>
                        <span class="text-sm font-medium text-gray-900">{{ $student->pkl->supervisor ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">Status</span>
                        @if(($student->pkl->status ?? null) === 'active')
                            <x-badge variant="success">Aktif</x-badge>
                        @else
                            <x-badge variant="neutral">Selesai</x-badge>
                        @endif
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">Mulai</span>
                        <span class="text-sm font-medium text-gray-900">{{ $student->pkl->start_date ? \Carbon\Carbon::parse($student->pkl->start_date)->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-500">Selesai</span>
                        <span class="text-sm font-medium text-gray-900">{{ $student->pkl->end_date ? \Carbon\Carbon::parse($student->pkl->end_date)->format('d/m/Y') : '-' }}</span>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Attendance History --}}
            <x-card>
                <x-slot name="header">Riwayat Kehadiran</x-slot>
                <x-slot name="subtitle">Kehadiran PKL terbaru</x-slot>

                <x-table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances ?? [] as $record)
                            <tr>
                                <td class="font-medium">{{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}</td>
                                <td>
                                    <x-badge variant="{{ $record->status === 'hadir' ? 'success' : ($record->status === 'izin' ? 'info' : ($record->status === 'sakit' ? 'warning' : 'danger')) }}">
                                        {{ strtoupper($record->status) }}
                                    </x-badge>
                                </td>
                                <td>{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                                <td>{{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="Belum ada riwayat" description="Siswa ini belum memiliki riwayat kehadiran PKL." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>

            {{-- GPS Location History --}}
            <x-card>
                <x-slot name="header">Riwayat Lokasi GPS</x-slot>
                <x-slot name="subtitle">Lokasi yang dikirim siswa</x-slot>

                <div class="mb-4 flex justify-end">
                    <a href="{{ route('pkl-supervisor.locations.show', $student->id) }}">
                        <x-button variant="ghost" size="sm">Lihat Peta</x-button>
                    </a>
                </div>

                <x-table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Akurasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations ?? [] as $loc)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($loc->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-sm">{{ $loc->latitude }}</td>
                                <td class="text-sm">{{ $loc->longitude }}</td>
                                <td class="text-sm">{{ $loc->accuracy ? number_format($loc->accuracy, 1) . 'm' : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="Belum ada lokasi" description="Siswa ini belum mengirim lokasi GPS." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>
        </div>
    </div>
</div>
@endsection

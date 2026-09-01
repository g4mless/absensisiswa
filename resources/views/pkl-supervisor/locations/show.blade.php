@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-pkl-supervisor')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('pkl-supervisor.locations.index') }}" class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Lokasi: {{ $student->user->name ?? '-' }}</h1>
            <p class="text-gray-500">Tracking GPS real-time</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Real-time GPS Display --}}
        <div class="lg:col-span-1 space-y-6">
            <x-card elevated>
                <x-slot name="header">Lokasi Terkini</x-slot>
                <div class="flex flex-col items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-100">
                        <svg class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0Z"/>
                        </svg>
                    </div>

                    <div class="w-full space-y-3">
                        <div class="text-center">
                            <p class="text-xs text-gray-500">Latitude</p>
                            <p class="text-lg font-bold text-gray-900">{{ $student->lastLocation->latitude ?? '---' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500">Longitude</p>
                            <p class="text-lg font-bold text-gray-900">{{ $student->lastLocation->longitude ?? '---' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500">Terakhir Update</p>
                            <p class="text-sm font-medium text-gray-700">{{ $student->lastLocation ? \Carbon\Carbon::parse($student->lastLocation->created_at)->diffForHumans() : '-' }}</p>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Student Info --}}
            <x-card>
                <x-slot name="header">Info Siswa</x-slot>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">NIS</span>
                        <span class="text-sm font-medium text-gray-900">{{ $student->nis ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">Perusahaan</span>
                        <span class="text-sm font-medium text-gray-900">{{ $student->pkl->company ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-500">Hari Ini</span>
                        @if($student->todayAttendance ?? null)
                            <x-badge variant="success">HADIR</x-badge>
                        @else
                            <x-badge variant="warning">BELUM ABSEN</x-badge>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Map & History --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Map Placeholder --}}
            <x-card elevated>
                <x-slot name="header">Peta Lokasi</x-slot>
                <div class="flex h-80 items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Peta lokasi siswa</p>
                        <p class="text-xs text-gray-400">Integrasikan dengan Google Maps / Leaflet</p>
                    </div>
                </div>
            </x-card>

            {{-- Location History --}}
            <x-card>
                <x-slot name="header">Riwayat Lokasi</x-slot>
                <x-slot name="subtitle">Lokasi yang dikirim hari ini</x-slot>

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
                                <td>{{ \Carbon\Carbon::parse($loc->created_at)->format('H:i:s') }}</td>
                                <td class="text-sm">{{ $loc->latitude }}</td>
                                <td class="text-sm">{{ $loc->longitude }}</td>
                                <td class="text-sm">{{ $loc->accuracy ? number_format($loc->accuracy, 1) . 'm' : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="Belum ada riwayat" description="Siswa ini belum mengirim lokasi hari ini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>

                @if(($locations ?? collect())->hasPages())
                    <div class="mt-4">
                        <x-pagination :paginator="$locations" />
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection

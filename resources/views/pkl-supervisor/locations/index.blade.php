@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-pkl-supervisor')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Lokasi Siswa PKL</h1>
        <p class="text-gray-500">Pantau lokasi GPS semua siswa PKL</p>
    </div>

    {{-- Map Placeholder --}}
    <x-card elevated>
        <x-slot name="header">Peta Lokasi</x-slot>
        <div class="flex h-96 items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                </svg>
                <p class="mt-3 text-sm text-gray-500">Peta lokasi semua siswa</p>
                <p class="text-xs text-gray-400">Integrasikan dengan Google Maps / Leaflet</p>
            </div>
        </div>
    </x-card>

    {{-- Students Location List --}}
    <x-card>
        <x-slot name="header">Lokasi Terkini</x-slot>
        <x-slot name="subtitle">Lokasi terakhir dari setiap siswa</x-slot>

        <x-table>
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Terakhir Update</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentLocations ?? [] as $location)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700">
                                    {{ substr($location->student->user->name ?? 'S', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $location->student->user->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $location->student->nis ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600">{{ $location->latitude }}</td>
                        <td class="text-sm text-gray-600">{{ $location->longitude }}</td>
                        <td class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($location->created_at)->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('pkl-supervisor.locations.show', $location->student_id) }}">
                                <x-button variant="ghost" size="sm">Detail</x-button>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-empty-state title="Tidak ada data" description="Belum ada siswa yang mengirim lokasi GPS." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
    </x-card>

    {{-- Alerts for students outside radius --}}
    @if(($outsideRadius ?? collect())->isNotEmpty())
        <x-card>
            <x-slot name="header">Peringatan Luar Radius</x-slot>
            <x-slot name="subtitle">Siswa yang berada di luar area PKL</x-slot>

            <div class="space-y-3">
                @foreach($outsideRadius as $alert)
                    <div class="flex items-center gap-3 rounded-lg border border-red-100 bg-red-50 p-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-red-800">{{ $alert->student->user->name ?? '-' }}</p>
                            <p class="text-xs text-red-600">{{ $alert->message }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif
</div>
@endsection

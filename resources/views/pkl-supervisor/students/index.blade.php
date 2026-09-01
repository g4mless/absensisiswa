@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-pkl-supervisor')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Siswa PKL</h1>
            <p class="text-gray-500">Daftar sis PKL yang menjadi bimbingan Anda</p>
        </div>
    </div>

    {{-- Search --}}
    <x-card>
        <form method="GET" action="{{ route('pkl-supervisor.students.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <x-search-input name="search" placeholder="Cari nama atau NIS siswa..." value="{{ request('search') }}" />
            </div>
            <x-button type="submit" variant="primary">Cari</x-button>
        </form>
    </x-card>

    {{-- Students Table --}}
    <x-card>
        <x-slot name="header">Daftar Siswa</x-slot>
        <x-slot name="subtitle">{{ $students->total() ?? 0 }} siswa ditemukan</x-slot>

        <x-table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIS</th>
                    <th>Perusahaan</th>
                    <th>Status PKL</th>
                    <th>GPS Terakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students ?? [] as $student)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700">
                                    {{ substr($student->user->name ?? 'S', 0, 1) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $student->user->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600">{{ $student->nis ?? '-' }}</td>
                        <td class="text-sm text-gray-600">{{ $student->pkl->company ?? '-' }}</td>
                        <td>
                            @if(($student->pkl->status ?? null) === 'active')
                                <x-badge variant="success">Aktif</x-badge>
                            @else
                                <x-badge variant="neutral">Selesai</x-badge>
                            @endif
                        </td>
                        <td class="text-sm text-gray-500">
                            {{ $student->lastGpsTime ? \Carbon\Carbon::parse($student->lastGpsTime)->diffForHumans() : '-' }}
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('pkl-supervisor.students.show', $student->id) }}">
                                    <x-button variant="ghost" size="sm">Detail</x-button>
                                </a>
                                <a href="{{ route('pkl-supervisor.locations.show', $student->id) }}">
                                    <x-button variant="ghost" size="sm">Lokasi</x-button>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-empty-state title="Tidak ada siswa" description="Tidak ada siswa PKL yang ditemukan." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        @if(($students ?? collect())->hasPages())
            <div class="mt-4">
                <x-pagination :paginator="$students" />
            </div>
        @endif
    </x-card>
</div>
@endsection

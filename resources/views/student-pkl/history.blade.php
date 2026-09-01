@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-student-pkl')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Riwayat</h1>
        <p class="text-gray-500">Riwayat kehadiran dan lokasi PKL</p>
    </div>

    {{-- Tabs --}}
    <div x-data="{ activeTab: 'attendance' }">
        <div class="flex gap-1 rounded-lg bg-gray-100 p-1">
            <button
                @click="activeTab = 'attendance'"
                class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors"
                :class="activeTab === 'attendance' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            >
                Absensi
            </button>
            <button
                @click="activeTab = 'location'"
                class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors"
                :class="activeTab === 'location' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            >
                Lokasi
            </button>
        </div>

        {{-- Attendance Tab --}}
        <div x-show="activeTab === 'attendance'" class="mt-6">
            {{-- Date Filter --}}
            <x-card>
                <form method="GET" action="{{ route('student-pkl.history') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <input type="hidden" name="tab" value="attendance">
                    <div class="flex-1">
                        <x-input label="Dari Tanggal" name="start_date" type="date" :value="request('start_date')" />
                    </div>
                    <div class="flex-1">
                        <x-input label="Sampai Tanggal" name="end_date" type="date" :value="request('end_date')" />
                    </div>
                    <div class="flex gap-2">
                        <x-button type="submit" variant="primary">Filter</x-button>
                        @if(request('start_date') || request('end_date'))
                            <a href="{{ route('student-pkl.history') }}">
                                <x-button variant="ghost">Reset</x-button>
                            </a>
                        @endif
                    </div>
                </form>
            </x-card>

            <x-card>
                <x-slot name="header">Riwayat Absensi PKL</x-slot>
                <x-slot name="subtitle">{{ $attendances->total() ?? 0 }} data ditemukan</x-slot>

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
                                    <x-empty-state title="Tidak ada data" description="Belum ada riwayat kehadiran PKL." />
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

        {{-- Location Tab --}}
        <div x-show="activeTab === 'location'" class="mt-6">
            <x-card>
                <x-slot name="header">Riwayat Lokasi</x-slot>
                <x-slot name="subtitle">{{ $locations->total() ?? 0 }} data lokasi</x-slot>

                <x-table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Tanggal</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Akurasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations ?? [] as $loc)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($loc->created_at)->format('H:i:s') }}</td>
                                <td class="font-medium">{{ \Carbon\Carbon::parse($loc->created_at)->format('d/m/Y') }}</td>
                                <td>{{ $loc->latitude }}</td>
                                <td>{{ $loc->longitude }}</td>
                                <td>{{ $loc->accuracy ? number_format($loc->accuracy, 1) . 'm' : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state title="Tidak ada data" description="Belum ada riwayat lokasi yang dikirim." />
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

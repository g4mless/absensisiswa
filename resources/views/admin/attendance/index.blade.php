@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Catatan Absensi</h1>
        <p class="text-gray-500">Lihat dan kelola catatan absensi</p>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif

    <x-card>
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex flex-col sm:flex-row gap-4 mb-4">
            <x-input label="Tanggal" name="date" type="date" value="{{ request('date', now()->format('Y-m-d')) }}" />
            <x-select label="Kelas" name="class_id" :options="$classes->pluck('name', 'id')->toArray()" placeholder="Semua Kelas" value="{{ request('class_id') }}" />
            <div class="flex items-end">
                <x-button variant="primary" type="submit">Filter</x-button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <x-table>
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Waktu Masuk</th>
                        <th>GPS</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $record)
                        <tr>
                            <td class="font-medium">{{ $record->student->name ?? '-' }}</td>
                            <td>{{ $record->student->class->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</td>
                            <td>
                                <x-badge variant="{{ $record->status === 'hadir' ? 'success' : ($record->status === 'terlambat' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($record->status) }}
                                </x-badge>
                            </td>
                            <td class="font-mono text-sm">{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                            <td>
                                @if($record->is_location_suspicious)
                                    <x-badge variant="danger" title="{{ implode(', ', $record->location_flags ?? []) }}">SPOOFING? ({{ $record->risk_score ?? 0 }})</x-badge>
                                @elseif(!empty($record->location_flags))
                                    <x-badge variant="warning" title="{{ implode(', ', $record->location_flags ?? []) }}">RISK ({{ $record->risk_score ?? 0 }})</x-badge>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                                @if(!is_null($record->sample_count))
                                    <div class="mt-1 text-xs text-gray-500 font-mono">{{ $record->sample_count }} smp / {{ $record->unique_coordinates }} uniq / {{ $record->max_spread_meters }} m / skor {{ $record->risk_score ?? 0 }}</div>
                                @endif
                            </td>
                            <td class="text-sm text-gray-500 max-w-[200px] truncate">{{ $record->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state title="Tidak ada catatan absensi ditemukan" description="Tidak ada catatan yang cocok dengan kriteria filter Anda." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$attendances" />
        </div>
    </x-card>
</div>
@endsection

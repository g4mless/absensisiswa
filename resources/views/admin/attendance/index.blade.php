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
                                <x-badge variant="{{ $record->status === 'present' ? 'success' : ($record->status === 'late' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($record->status) }}
                                </x-badge>
                            </td>
                            <td class="font-mono text-sm">{{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}</td>
                            <td class="text-sm text-gray-500 max-w-[200px] truncate">{{ $record->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
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

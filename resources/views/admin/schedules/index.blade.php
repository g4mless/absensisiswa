@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jadwal</h1>
            <p class="text-gray-500">Kelola jadwal kelas</p>
        </div>
        <a href="{{ route('admin.schedules.create') }}">
                <x-button variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Jadwal
                </x-button>
        </a>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif

    <x-card>
        <div class="flex flex-col sm:flex-row gap-4 mb-4">
            <form method="GET" action="{{ route('admin.schedules.index') }}">
                <x-select name="day" :options="['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu']" placeholder="Semua Hari" value="{{ request('day') }}" />
            </form>
            <form method="GET" action="{{ route('admin.schedules.index') }}">
                <x-select name="class_id" :options="$classes->pluck('name', 'id')->toArray()" placeholder="Semua Kelas" value="{{ request('class_id') }}" />
            </form>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th>Waktu</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru</th>
                        <th>Kelas</th>
                        <th>Ruang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                        <tr>
                            <td>
                                <x-badge variant="info">{{ $schedule->day }}</x-badge>
                            </td>
                            <td class="font-mono text-sm">{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                            <td class="font-medium">{{ $schedule->subject->name ?? '-' }}</td>
                            <td>{{ $schedule->teacher->name ?? '-' }}</td>
                            <td>{{ $schedule->class->name ?? '-' }}</td>
                            <td>{{ $schedule->room ?? '-' }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.schedules.edit', $schedule) }}">
                                        <x-button variant="ghost" size="sm">Edit</x-button>
                                    </a>
                                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="danger" size="sm" type="submit">Hapus</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state title="Tidak ada jadwal ditemukan" description="Mulai dengan menambahkan jadwal baru." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$schedules" />
        </div>
    </x-card>
</div>
@endsection

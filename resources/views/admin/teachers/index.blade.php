@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Guru</h1>
            <p class="text-gray-500">Kelola data guru dan penugasan</p>
        </div>
        <a href="{{ route('admin.teachers.create') }}">
                <x-button variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Guru
                </x-button>
        </a>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif

    <x-card>
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.teachers.index') }}">
                <x-search-input name="search" placeholder="Cari berdasarkan NIP atau nama..." value="{{ request('search') }}" />
            </form>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Mata Pelajaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                        <tr>
                            <td class="font-mono text-sm">{{ $teacher->nip }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-info-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-info-600">{{ substr($teacher->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ $teacher->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $teacher->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($teacher->subjects as $subject)
                                        <x-badge variant="info">{{ $subject->name }}</x-badge>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <x-badge variant="{{ $teacher->is_active ? 'success' : 'neutral' }}">
                                    {{ $teacher->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </x-badge>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.teachers.edit', $teacher) }}">
                                        <x-button variant="ghost" size="sm">Edit</x-button>
                                    </a>
                                    <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="danger" size="sm" type="submit">Hapus</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="Tidak ada guru ditemukan" description="Tidak ada guru yang cocok dengan kriteria pencarian Anda." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$teachers" />
        </div>
    </x-card>
</div>
@endsection

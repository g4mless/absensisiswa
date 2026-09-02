@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Siswa</h1>
            <p class="text-gray-500">Kelola data siswa dan pendaftaran</p>
        </div>
        <a href="{{ route('admin.students.create') }}">
                <x-button variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Siswa
                </x-button>
        </a>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif

    <x-card>
        <div class="flex flex-col sm:flex-row gap-4 mb-4">
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex-1">
                <x-search-input name="search" placeholder="Cari berdasarkan NIS atau nama..." value="{{ request('search') }}" />
            </form>
            <form method="GET" action="{{ route('admin.students.index') }}">
                <x-select name="class_id" :options="$classes->pluck('name', 'id')->toArray()" placeholder="Semua Kelas" value="{{ request('class_id') }}" />
            </form>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td class="font-mono text-sm">{{ $student->nis }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-success-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-success-600">{{ substr($student->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ $student->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $student->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->class->name ?? '-' }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($student->is_pkl)
                                        <x-badge variant="info">PKL</x-badge>
                                    @endif
                                    <a href="{{ route('admin.students.edit', $student) }}">
                                        <x-button variant="ghost" size="sm">Edit</x-button>
                                    </a>
                                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="danger" size="sm" type="submit">Hapus</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state title="Tidak ada siswa ditemukan" description="Tidak ada siswa yang cocok dengan kriteria pencarian Anda." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$students" />
        </div>
    </x-card>
</div>
@endsection

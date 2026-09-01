@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kepala Program</h1>
            <p class="text-gray-500">Kelola penugasan kepala program/jurusan</p>
        </div>
        <a href="{{ route('admin.program-heads.create') }}">
                <x-button variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Penugasan
                </x-button>
        </a>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif

    <x-card>
        <div class="overflow-x-auto">
            <x-table>
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programHeads as $assignment)
                        <tr>
                            <td class="font-medium">{{ $assignment->teacher->name ?? '-' }}</td>
                            <td>
                                <x-badge variant="info">{{ $assignment->major->name ?? '-' }}</x-badge>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('admin.program-heads.destroy', $assignment) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="danger" size="sm" type="submit">Hapus</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-empty-state title="Tidak ada penugasan ditemukan" description="Mulai dengan menugaskan kepala program." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$programHeads" />
        </div>
    </x-card>
</div>
@endsection

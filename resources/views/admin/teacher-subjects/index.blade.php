@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Penugasan Mata Pelajaran Guru</h1>
            <p class="text-gray-500">Tugaskan mata pelajaran kepada guru untuk kelas tertentu</p>
        </div>
        <a href="{{ route('admin.teacher-subjects.create') }}">
                <x-button variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Penugasan
                </x-button>
        </a>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif

    <x-card x-data="{
        selected: [],
        allItemIds: {{ $teacherSubjects->getCollection()->pluck('id')->toJson() }},
        get allSelected() {
            return this.allItemIds.length > 0 && this.selected.length === this.allItemIds.length;
        },
        set allSelected(value) {
            this.selected = value ? [...this.allItemIds] : [];
        }
    }">
        <div x-show="selected.length > 0" x-cloak class="mb-4 p-3 bg-primary-50 border border-primary-200 rounded-lg flex items-center gap-3">
            <span class="text-sm text-primary-700" x-text="selected.length + ' item dipilih'"></span>
            <button type="button" @click="
                if(confirm('Hapus ' + selected.length + ' penugasan yang dipilih?')) {
                    fetch('{{ route('admin.teacher-subjects.bulk-destroy') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: selected })
                    }).then(r => r.json()).then(data => {
                        if(data.errors) { alert('Gagal menghapus: ' + Object.values(data.errors).flat().join(', ')); }
                        else { window.location.reload(); }
                    }).catch(() => { window.location.reload(); });
                }
            " class="text-sm text-red-600 font-medium hover:underline">Hapus Terpilih</button>
            <button type="button" @click="selected = []" class="text-sm text-primary-600 hover:underline">Batal</button>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <thead>
                    <tr>
                        <th class="w-12">
                            <input type="checkbox" x-model="allSelected" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th>Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teacherSubjects as $assignment)
                        <tr>
                            <td>
                                <input type="checkbox" value="{{ $assignment->id }}" x-model="selected" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="font-medium">{{ $assignment->teacher->name ?? '-' }}</td>
                            <td>
                                <x-badge variant="info">{{ $assignment->subject->name ?? '-' }}</x-badge>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($assignment->classes as $class)
                                        <x-badge variant="neutral">{{ $class->name }}</x-badge>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.teacher-subjects.edit', $assignment) }}">
                                        <x-button variant="ghost" size="sm">Edit</x-button>
                                    </a>
                                    <form action="{{ route('admin.teacher-subjects.destroy', $assignment) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
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
                                <x-empty-state title="Tidak ada penugasan ditemukan" description="Mulai dengan menugaskan mata pelajaran kepada guru." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$teacherSubjects" />
        </div>
    </x-card>
</div>
@endsection

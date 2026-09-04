@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Kelas</h1>
            <p class="text-gray-500">Kelola kelas sekolah</p>
        </div>
        <a href="{{ route('admin.classes.create') }}">
                <x-button variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kelas
                </x-button>
        </a>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif

    @php
        $grouped = $classes->getCollection()->groupBy(fn ($class) => $class->major?->name ?? 'Lainnya');
    @endphp

    <x-card x-data="{
        selected: [],
        allItemIds: {{ $classes->getCollection()->pluck('id')->toJson() }},
        get allSelected() {
            return this.allItemIds.length > 0 && this.selected.length === this.allItemIds.length;
        },
        set allSelected(value) {
            this.selected = value ? [...this.allItemIds] : [];
        },
        toggleCategory(ids) {
            const allSelected = ids.every(id => this.selected.includes(id));
            if (allSelected) {
                this.selected = this.selected.filter(id => !ids.includes(id));
            } else {
                this.selected = [...new Set([...this.selected, ...ids])];
            }
        },
        categoryAllSelected(ids) {
            return ids.length > 0 && ids.every(id => this.selected.includes(id));
        }
    }">
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.classes.index') }}">
                <x-search-input name="search" placeholder="Cari berdasarkan nama kelas atau jurusan..." value="{{ request('search') }}" />
            </form>
        </div>

        <div x-show="selected.length > 0" x-cloak class="mb-4 p-3 bg-primary-50 border border-primary-200 rounded-lg flex items-center gap-3">
            <span class="text-sm text-primary-700" x-text="selected.length + ' item dipilih'"></span>
            <button type="button" @click="
                if(confirm('Hapus ' + selected.length + ' kelas yang dipilih?')) {
                    fetch('{{ route('admin.classes.bulk-destroy') }}', {
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
            <button type="button" x-show="selected.length > 10" @click="if (confirm('PERINGATAN: Hapus semua kelas? Tindakan ini tidak dapat dibatalkan.')) fetch('{{ route('admin.classes.all-destroy') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => window.location.reload())" class="text-sm text-red-600 font-medium hover:underline">Hapus Semuanya</button>
            <button type="button" @click="selected = []" class="text-sm text-primary-600 hover:underline">Batal</button>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <thead>
                    <tr>
                        <th class="w-12">
                            <input type="checkbox" x-model="allSelected" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th>Nama</th>
                        <th>Jurusan</th>
                        <th>Wali Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                @forelse($grouped as $major => $items)
                    @php $catIds = $items->pluck('id')->toArray(); @endphp
                    <tbody>
                        <tr class="bg-gray-50">
                            <td colspan="5" class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox"
                                           x-effect="$el.checked = categoryAllSelected({{ json_encode($catIds) }})"
                                           @click.prevent="toggleCategory({{ json_encode($catIds) }})"
                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="font-semibold text-sm text-gray-700">{{ $major ?: 'Lainnya' }}</span>
                                    <span class="text-xs text-gray-500">({{ $items->count() }} kelas)</span>
                                </div>
                            </td>
                        </tr>
                        @foreach($items as $class)
                            <tr>
                                <td>
                                    <input type="checkbox" value="{{ $class->id }}" x-model="selected" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="font-medium">{{ $class->name }}</td>
                                <td>
                                    <x-badge variant="info">{{ $class->major?->code ?? '-' }}</x-badge>
                                </td>
                                <td>{{ $class->homeroomTeacher?->teacher?->name ?? '-' }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.classes.edit', $class) }}">
                                            <x-button variant="ghost" size="sm">Edit</x-button>
                                        </a>
                                        <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-button variant="danger" size="sm" type="submit">Hapus</x-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="Tidak ada kelas ditemukan" description="Mulai dengan menambahkan kelas baru." />
                            </td>
                        </tr>
                    </tbody>
                @endforelse
            </x-table>
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$classes" />
        </div>
    </x-card>
</div>
@endsection

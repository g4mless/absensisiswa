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
        <div class="flex flex-wrap gap-2 items-center">
            <a href="{{ route('admin.teachers.export') }}">
                <x-button variant="success">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Ekspor
                </x-button>
            </a>
            <div x-data="{
                open: false,
                loading: false,
                sheets: [],
                selectedSheets: [],
                async readSheets(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.loading = true;
                    this.sheets = [];
                    this.selectedSheets = [];
                    const form = new FormData();
                    form.append('file', file);
                    form.append('_token', '{{ csrf_token() }}');
                    const response = await fetch('{{ route('admin.teachers.import.sheets') }}', { method: 'POST', body: form });
                    const data = await response.json();
                    this.sheets = data.sheets || [];
                    this.loading = false;
                }
            }" class="relative">
                <x-button variant="warning" x-on:click="open = !open">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Impor
                </x-button>
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="open = false">
                    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4 shadow-xl" @click.stop>
                        <h3 class="text-lg font-semibold mb-4">Impor Data Guru</h3>
                        <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-2">Gunakan file jadwal dengan sheet distribusi dan Walas 26-27.</p>
                                <input type="file" name="file" accept=".xlsx" required @change="readSheets" class="block w-full text-sm text-gray-600 border border-gray-300 rounded-lg p-2">
                            </div>
                            <div x-show="loading" class="text-sm text-gray-500 mb-4">Membaca worksheet...</div>
                            <div x-show="sheets.length > 0" x-cloak class="mb-4 space-y-2">
                                <p class="text-sm font-medium text-gray-700">Pilih worksheet:</p>
                                <template x-for="sheet in sheets" :key="sheet">
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="sheets[]" :value="sheet" x-model="selectedSheets" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <span x-text="sheet"></span>
                                    </label>
                                </template>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                                <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">Impor</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.teachers.create') }}">
                <x-button variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Guru
                </x-button>
            </a>
        </div>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert variant="danger" title="Import gagal" dismissible>{{ session('error') }}</x-alert>
    @endif

    <x-card x-data="{
        selected: [],
        allItemIds: {{ $teachers->getCollection()->pluck('id')->toJson() }},
        get allSelected() {
            return this.allItemIds.length > 0 && this.selected.length === this.allItemIds.length;
        },
        set allSelected(value) {
            this.selected = value ? [...this.allItemIds] : [];
        }
    }">
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.teachers.index') }}">
                <x-search-input name="search" placeholder="Cari berdasarkan NIP atau nama..." value="{{ request('search') }}" />
            </form>
        </div>

        <div x-show="selected.length > 0" x-cloak class="mb-4 p-3 bg-primary-50 border border-primary-200 rounded-lg flex items-center gap-3">
            <span class="text-sm text-primary-700" x-text="selected.length + ' item dipilih'"></span>
            <button type="button" @click="
                if(confirm('Hapus ' + selected.length + ' guru yang dipilih?')) {
                    fetch('{{ route('admin.teachers.bulk-destroy') }}', {
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
            <button type="button" x-show="selected.length > 10" @click="if (confirm('PERINGATAN: Hapus semua guru? Tindakan ini tidak dapat dibatalkan.')) fetch('{{ route('admin.teachers.all-destroy') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => window.location.reload())" class="text-sm text-red-600 font-medium hover:underline">Hapus Semuanya</button>
            <button type="button" @click="selected = []" class="text-sm text-primary-600 hover:underline">Batal</button>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <thead>
                    <tr>
                        <th class="w-12">
                            <input type="checkbox" x-model="allSelected" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Mata Pelajaran</th>
                        <th>Kepala Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                        <tr>
                                <td>
                                    <input type="checkbox" value="{{ $teacher->id }}" x-model="selected" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="font-mono text-sm">{{ $teacher->nip }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-info-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-info-600">{{ substr($teacher->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ $teacher->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($teacher->subjects->unique('id') as $subject)
                                            <x-badge variant="info">{{ $subject->name }}</x-badge>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @if($teacher->programHead)
                                        <x-badge variant="warning">{{ $teacher->programHead->major->name ?? '-' }}</x-badge>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
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
                            <td colspan="6">
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

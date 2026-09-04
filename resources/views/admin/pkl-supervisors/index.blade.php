@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pembimbing PKL</h1>
            <p class="text-gray-500">Kelola penugasan pembimbing PKL/magang</p>
        </div>
        <a href="{{ route('admin.pkl-supervisors.create') }}">
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
        allItemIds: {{ $pklSupervisors->getCollection()->pluck('id')->toJson() }},
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
                    fetch('{{ route('admin.pkl-supervisors.bulk-destroy') }}', {
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
            <button type="button" x-show="selected.length > 10" @click="if (confirm('PERINGATAN: Hapus semua penugasan pembimbing PKL? Tindakan ini tidak dapat dibatalkan.')) fetch('{{ route('admin.pkl-supervisors.all-destroy') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => window.location.reload())" class="text-sm text-red-600 font-medium hover:underline">Hapus Semuanya</button>
            <button type="button" @click="selected = []" class="text-sm text-primary-600 hover:underline">Batal</button>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <thead>
                    <tr>
                        <th class="w-12">
                            <input type="checkbox" x-model="allSelected" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th>Pembimbing PKL</th>
                        <th>Perusahaan/Tempat</th>
                        <th>Nomor Kontak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pklSupervisors as $assignment)
                        <tr>
                            <td>
                                <input type="checkbox" value="{{ $assignment->id }}" x-model="selected" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="font-medium">{{ $assignment->supervisor_name }}</td>
                            <td>{{ $assignment->company_name ?? '-' }}</td>
                            <td>{{ $assignment->contact_phone ?? '-' }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.pkl-supervisors.edit', $assignment) }}">
                                        <x-button variant="ghost" size="sm">Edit</x-button>
                                    </a>
                                    <form action="{{ route('admin.pkl-supervisors.destroy', $assignment) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
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
                                <x-empty-state title="Tidak ada penugasan ditemukan" description="Mulai dengan menugaskan pembimbing PKL." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$pklSupervisors" />
        </div>
    </x-card>
</div>
@endsection

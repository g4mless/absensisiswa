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
        <div class="flex flex-wrap gap-2 items-center">
            <a href="{{ route('admin.students.export') }}">
                <x-button variant="success">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Ekspor
                </x-button>
            </a>
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="md-btn-warning">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Impor
                </button>
                <div x-show="open" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="open = false">
                    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4 shadow-xl" @click.stop>
                        <h3 class="text-lg font-semibold mb-4">Impor Data Siswa (DAPODIK)</h3>
                        <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            @csrf
                            <div class="mb-4 space-y-2">
                                <p class="text-sm text-gray-500">Format file: .XLS (Excel 97-2003)</p>
                                <p class="text-sm text-gray-500">Sheet per jurusan: TKJ, TSM, TITL, DKV, DPB, MP</p>
                                <p class="text-sm text-gray-500">Kolom yang dibutuhkan:</p>
                                <ul class="text-xs text-gray-400 list-disc list-inside space-y-0.5">
                                    <li><strong>D</strong> - NISN</li>
                                    <li><strong>F</strong> - Nama Siswa</li>
                                </ul>
                                <x-file-upload name="file" label="Pilih File Excel" accept=".xls,.xlsx" required :error="$errors->first('file')" />
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="open = false" class="md-btn-ghost">Batal</button>
                                <button type="submit" class="md-btn-primary" x-bind:disabled="submitting" x-bind:class="submitting ? 'opacity-60 cursor-wait' : ''">
                                    <span x-show="!submitting">Impor</span>
                                    <span x-show="submitting">Memproses...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.students.create') }}">
                <x-button variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Siswa
                </x-button>
            </a>
        </div>
    </div>

    @if(session('status'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('status') }}</x-alert>
    @endif
    @if($errors->has('file'))
        <x-alert variant="danger" title="Impor gagal" dismissible>{{ $errors->first('file') }}</x-alert>
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

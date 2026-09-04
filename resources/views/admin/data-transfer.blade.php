@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Import/Ekspor Data</h1>
        <p class="text-gray-500">Kelola proses bulk import dan ekspor data aplikasi.</p>
    </div>

    <div class="space-y-6">
        <x-card x-data="{
            sheets: [],
            selectedSheets: [],
            loading: false,
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
                this.selectedSheets = [...this.sheets];
                this.loading = false;
            }
        }">
            <x-slot name="header">Import Workbook Akademik</x-slot>
            <x-slot name="subtitle">Satu file workbook dapat mengisi beberapa data sesuai worksheet yang dipilih.</x-slot>
            <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data" @submit="if ((selectedSheets.includes('distribusi') || selectedSheets.includes('PKL')) && !confirm('Import ini akan menghapus data lama sesuai worksheet yang dipilih. Lanjutkan?')) $event.preventDefault()" class="mt-4 space-y-3">
                @csrf
                <input type="file" name="file" accept=".xlsx" required @change="readSheets" class="block w-full text-sm text-gray-600 border border-gray-300 rounded-lg p-2">
                <p x-show="loading" class="text-sm text-gray-500">Membaca worksheet...</p>
                <div x-show="sheets.length > 0" x-cloak class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">Pilih worksheet:</p>
                    <template x-for="sheet in sheets" :key="sheet">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="sheets[]" :value="sheet" x-model="selectedSheets" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span x-text="sheet"></span>
                        </label>
                    </template>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 text-sm text-gray-600 space-y-1">
                    <p class="font-medium text-gray-800">Data yang dihasilkan:</p>
                    <p><strong>distribusi:</strong> guru, mata pelajaran, relasi guru-kelas, dan NIP.</p>
                    <p><strong>10 / 11:</strong> jadwal KBM kelas X dan XI.</p>
                    <p><strong>Walas 26-27:</strong> wali kelas dan kepala program.</p>
                    <p><strong>PKL:</strong> guru pembimbing PKL dan penugasan berdasarkan kelas XII.</p>
                    <p class="pt-1 text-xs text-gray-500">Worksheet yang tidak dicentang tidak akan diproses.</p>
                </div>
                <div x-show="selectedSheets.includes('distribusi') || selectedSheets.includes('PKL')" x-cloak class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    <p class="font-medium">Perhatian: import bersifat replace.</p>
                    <p x-show="selectedSheets.includes('distribusi')">Worksheet distribusi akan menghapus seluruh data guru lama beserta relasinya.</p>
                    <p x-show="selectedSheets.includes('PKL')">Worksheet PKL akan menghapus seluruh penugasan pembimbing PKL lama.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 transition-colors">Impor Workbook Terpilih</button>
                </div>
            </form>
        </x-card>

        <x-card>
            <x-slot name="header">Ekspor Data</x-slot>
            <x-slot name="subtitle">Unduh data yang sudah tersimpan sesuai kebutuhan.</x-slot>
            <div class="flex flex-wrap gap-2 mt-4">
                <a href="{{ route('admin.teachers.export') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">Ekspor Guru</a>
                <a href="{{ route('admin.pkl-supervisors.export') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">Ekspor Pembimbing PKL</a>
            </div>
        </x-card>
    </div>
</div>
@endsection

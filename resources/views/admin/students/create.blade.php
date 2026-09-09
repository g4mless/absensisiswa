@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.students.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Siswa</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Tambah Siswa Baru</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.students.store') }}" method="POST">
            @csrf
            <div class="space-y-4" x-data="{ isPkl: {{ old('is_pkl') ? 'true' : 'false' }} }">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input label="NIS (Identitas Siswa)" name="nis" :error="$errors->first('nis')" value="{{ old('nis') }}" />
                    <x-input label="Nama Lengkap" name="name" :error="$errors->first('name')" value="{{ old('name') }}" />
                </div>
                <x-select label="Kelas" name="class_id" :options="$classes->pluck('name', 'id')->toArray()" placeholder="Pilih Kelas" :error="$errors->first('class_id')" value="{{ old('class_id') }}" />
                <x-input label="Telepon" name="phone" type="tel" :error="$errors->first('phone')" value="{{ old('phone') }}" />
                <x-input label="Alamat" name="address" :error="$errors->first('address')" value="{{ old('address') }}" />
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_pkl" value="1" x-model="isPkl" {{ old('is_pkl') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                    <label class="text-sm text-gray-700">Siswa PKL (Praktek Kerja Lapangan)</label>
                </div>
                <div x-show="isPkl" x-cloak class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-4">
                    <p class="text-sm font-semibold text-gray-700">Detail Penempatan PKL <span class="font-normal text-gray-400">(opsional)</span></p>
                    <x-input label="Tempat PKL" name="tempat_pkl" :error="$errors->first('tempat_pkl')" value="{{ old('tempat_pkl') }}" />
                    <div>
                        <label for="pembimbing_id" class="block text-sm font-medium text-gray-700 mb-1">Guru Pembimbing</label>
                        <select id="pembimbing_id" name="pembimbing_id" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Pilih pembimbing</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('pembimbing_id') == $teacher->id)>{{ $teacher->user->name ?? '-' }} ({{ $teacher->nip }})</option>
                            @endforeach
                        </select>
                        @error('pembimbing_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input label="Tanggal Mulai" name="pkl_start_date" type="date" :error="$errors->first('pkl_start_date')" value="{{ old('pkl_start_date') }}" />
                        <x-input label="Tanggal Selesai" name="pkl_end_date" type="date" :error="$errors->first('pkl_end_date')" value="{{ old('pkl_end_date') }}" />
                    </div>
                    <x-select label="Status PKL" name="pkl_status" :options="['PLANNED' => 'PLANNED', 'ACTIVE' => 'ACTIVE', 'COMPLETED' => 'COMPLETED', 'CANCELLED' => 'CANCELLED']" placeholder="Pilih status" :error="$errors->first('pkl_status')" value="{{ old('pkl_status', 'ACTIVE') }}" />
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Buat Siswa</x-button>
                <a href="{{ route('admin.students.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

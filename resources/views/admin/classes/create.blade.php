@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.classes.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Kelas</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Tambah Kelas Baru</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.classes.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <x-select label="Tingkat" name="grade" :options="['X' => 'X', 'XI' => 'XI', 'XII' => 'XII']" :error="$errors->first('grade')" value="{{ old('grade') }}" placeholder="Pilih tingkat" />
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="major_id" class="md-label">Jurusan</label>
                        <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'add-major')" class="text-xs font-medium text-primary-600 hover:text-primary-500 hover:underline">+ Tambah Jurusan</button>
                    </div>
                    <x-select name="major_id" :options="$majors->pluck('name', 'id')->toArray()" :error="$errors->first('major_id')" value="{{ old('major_id') }}" placeholder="Pilih jurusan" />
                </div>
                <x-input label="Rombel" name="section" :error="$errors->first('section')" value="{{ old('section') }}" placeholder="contoh: 1" />
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Buat Kelas</x-button>
                <a href="{{ route('admin.classes.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>

@include('admin.classes._major-quick-add')
@endsection

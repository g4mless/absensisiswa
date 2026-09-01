@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.program-heads.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Kepala Program</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Tambah Penugasan Kepala Program</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.program-heads.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <x-select label="Guru" name="teacher_id" :options="$teachers->pluck('name', 'id')->toArray()" placeholder="Pilih Guru" :error="$errors->first('teacher_id')" value="{{ old('teacher_id') }}" />
                <x-select label="Jurusan" name="major_id" :options="$majors->pluck('name', 'id')->toArray()" placeholder="Pilih Jurusan" :error="$errors->first('major_id')" value="{{ old('major_id') }}" />
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Buat Penugasan</x-button>
                <a href="{{ route('admin.program-heads.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

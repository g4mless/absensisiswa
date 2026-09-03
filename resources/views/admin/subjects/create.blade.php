@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.subjects.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Mata Pelajaran</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Tambah Mata Pelajaran Baru</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.subjects.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <x-input label="Nama Mata Pelajaran" name="name" :error="$errors->first('name')" value="{{ old('name') }}" placeholder="contoh: Matematika" />
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Buat Mata Pelajaran</x-button>
                <a href="{{ route('admin.subjects.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

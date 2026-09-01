@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.classes.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Kelas</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Kelas: {{ $class->name }}</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.classes.update', $class) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <x-input label="Nama Kelas" name="name" :error="$errors->first('name')" value="{{ old('name', $class->name) }}" placeholder="contoh: XII RPL 1" />
                <x-input label="Jurusan" name="major" :error="$errors->first('major')" value="{{ old('major', $class->major) }}" placeholder="contoh: RPL" />
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Perbarui Kelas</x-button>
                <a href="{{ route('admin.classes.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

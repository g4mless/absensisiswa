@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.majors.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Jurusan</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Jurusan: {{ $major->name }}</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.majors.update', $major) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <x-input label="Nama Jurusan" name="name" :error="$errors->first('name')" value="{{ old('name', $major->name) }}" placeholder="contoh: Rekayasa Perangkat Lunak" />
                <x-input label="Kode" name="code" :error="$errors->first('code')" value="{{ old('code', $major->code) }}" placeholder="contoh: RPL" />
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Perbarui Jurusan</x-button>
                <a href="{{ route('admin.majors.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.academic-years.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Tahun Akademik</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Tahun Akademik: {{ $academicYear->year }}</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.academic-years.update', $academicYear) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <x-input label="Tahun" name="year" :error="$errors->first('year')" value="{{ old('year', $academicYear->year) }}" placeholder="contoh: 2025/2026" />
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $academicYear->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                    <label class="text-sm text-gray-700">Atur sebagai Tahun Aktif</label>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Perbarui Tahun Akademik</x-button>
                <a href="{{ route('admin.academic-years.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.teacher-subjects.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Penugasan</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Tambah Penugasan Mata Pelajaran Guru</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.teacher-subjects.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <x-select label="Guru" name="teacher_id" :options="$teachers->pluck('name', 'id')->toArray()" placeholder="Pilih Guru" :error="$errors->first('teacher_id')" value="{{ old('teacher_id') }}" />
                <x-select label="Mata Pelajaran" name="subject_id" :options="$subjects->pluck('name', 'id')->toArray()" placeholder="Pilih Mata Pelajaran" :error="$errors->first('subject_id')" value="{{ old('subject_id') }}" />
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($classes as $class)
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" {{ in_array($class->id, old('class_ids', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                                <label class="text-sm text-gray-700">{{ $class->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('class_ids')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Buat Penugasan</x-button>
                <a href="{{ route('admin.teacher-subjects.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

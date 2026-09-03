@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.teacher-subjects.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Penugasan</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Penugasan Mata Pelajaran Guru</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.teacher-subjects.update', $assignment) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <x-teacher-autocomplete :teachers="$teachers" :selected="$assignment->teacher_id" :error="$errors->first('teacher_name')" />
                <x-select label="Mata Pelajaran" name="subject_id" :options="$subjects->pluck('name', 'id')->toArray()" placeholder="Pilih Mata Pelajaran" :error="$errors->first('subject_id')" value="{{ old('subject_id', $assignment->subject_id) }}" />
                <x-select label="Kelas" name="class_id" :options="$classes->pluck('name', 'id')->toArray()" placeholder="Pilih Kelas" :error="$errors->first('class_id')" value="{{ old('class_id', $assignment->class_id) }}" />
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Simpan Perubahan</x-button>
                <a href="{{ route('admin.teacher-subjects.index') }}"><x-button variant="ghost">Batal</x-button></a>
            </div>
        </form>
    </x-card>
</div>
@endsection

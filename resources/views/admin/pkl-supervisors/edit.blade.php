@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.pkl-supervisors.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Pembimbing PKL</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Penugasan Pembimbing PKL</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.pkl-supervisors.update', $assignment) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-1">Guru Pembimbing</label>
                    <select id="teacher_id" name="teacher_id" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id', $assignment->teacher_id) == $teacher->id)>{{ $teacher->user->name }} ({{ $teacher->nip }})</option>
                        @endforeach
                    </select>
                    @error('teacher_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                    <select id="class_id" name="class_id" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('class_id', $assignment->class_id) == $class->id)>{{ $class->grade }} {{ $class->major->code }} {{ $class->section }}</option>
                        @endforeach
                    </select>
                    @error('class_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Simpan Perubahan</x-button>
                <a href="{{ route('admin.pkl-supervisors.index') }}"><x-button variant="ghost">Batal</x-button></a>
            </div>
        </form>
    </x-card>
</div>
@endsection

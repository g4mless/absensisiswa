@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.teachers.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Guru</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Guru: {{ $teacher->name }}</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.teachers.update', $teacher) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input label="NIP (Identitas Guru)" name="nip" :error="$errors->first('nip')" value="{{ old('nip', $teacher->nip) }}" />
                    <x-input label="Nama Lengkap" name="name" :error="$errors->first('name')" value="{{ old('name', $teacher->name) }}" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input label="Telepon" name="phone" :error="$errors->first('phone')" value="{{ old('phone', $teacher->phone) }}" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mata Pelajaran</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($subjects as $subject)
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" {{ in_array($subject->id, old('subject_ids', $teacher->subjects->pluck('id')->toArray())) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                                <label class="text-sm text-gray-700">{{ $subject->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('subject_ids')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Perbarui Guru</x-button>
                <a href="{{ route('admin.teachers.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

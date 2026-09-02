@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.students.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Siswa</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Siswa: {{ $student->name }}</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.students.update', $student) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input label="NIS (Identitas Siswa)" name="nis" :error="$errors->first('nis')" value="{{ old('nis', $student->nis) }}" />
                    <x-input label="Nama Lengkap" name="name" :error="$errors->first('name')" value="{{ old('name', $student->name) }}" />
                </div>
                <x-select label="Kelas" name="class_id" :options="$classes->pluck('name', 'id')->toArray()" placeholder="Pilih Kelas" :error="$errors->first('class_id')" value="{{ old('class_id', $student->class_id) }}" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input label="Email" name="email" type="email" :error="$errors->first('email')" value="{{ old('email', $student->email) }}" />
                    <x-input label="Telepon" name="phone" :error="$errors->first('phone')" value="{{ old('phone', $student->phone) }}" />
                </div>
                <x-input label="Alamat" name="address" :error="$errors->first('address')" value="{{ old('address', $student->address) }}" />
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_pkl" value="1" {{ old('is_pkl', $student->is_pkl) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                        <label class="text-sm text-gray-700">Siswa PKL (Praktek Kerja Lapangan)</label>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Perbarui Siswa</x-button>
                <a href="{{ route('admin.students.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

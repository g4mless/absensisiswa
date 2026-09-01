@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Waktu Absensi</h1>
        <p class="text-gray-500">Tentukan jam mulai dan berakhirnya absensi siswa setiap hari.</p>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif

    <x-card>
        <form action="{{ route('admin.attendance-setting.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-input label="Mulai absensi" name="start_time" type="time" :error="$errors->first('start_time')" value="{{ old('start_time', substr($setting->start_time, 0, 5)) }}" />
                <x-input label="Berakhir absensi" name="end_time" type="time" :error="$errors->first('end_time')" value="{{ old('end_time', substr($setting->end_time, 0, 5)) }}" />
            </div>
            <p class="mt-2 text-xs text-gray-500">Jam berakhir harus lebih besar daripada jam mulai.</p>
            <div class="mt-6">
                <x-button variant="primary" type="submit">Simpan Pengaturan</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

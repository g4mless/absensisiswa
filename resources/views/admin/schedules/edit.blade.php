@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.schedules.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Jadwal</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Jadwal</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <x-select label="Hari" name="day" :options="['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat']" placeholder="Pilih Hari" :error="$errors->first('day')" value="{{ old('day', $schedule->day) }}" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input label="Waktu Mulai" name="start_time" type="time" :error="$errors->first('start_time')" value="{{ old('start_time', $schedule->start_time) }}" />
                    <x-input label="Waktu Selesai" name="end_time" type="time" :error="$errors->first('end_time')" value="{{ old('end_time', $schedule->end_time) }}" />
                </div>
                <x-select label="Mata Pelajaran" name="subject_id" :options="$subjects->pluck('name', 'id')->toArray()" placeholder="Pilih Mata Pelajaran" :error="$errors->first('subject_id')" value="{{ old('subject_id', $schedule->subject_id) }}" />
                <x-select label="Guru" name="teacher_id" :options="$teachers->pluck('name', 'id')->toArray()" placeholder="Pilih Guru" :error="$errors->first('teacher_id')" value="{{ old('teacher_id', $schedule->teacher_id) }}" />
                <x-select label="Kelas" name="class_id" :options="$classes->pluck('name', 'id')->toArray()" placeholder="Pilih Kelas" :error="$errors->first('class_id')" value="{{ old('class_id', $schedule->class_id) }}" />
                <x-input label="Ruang" name="room" :error="$errors->first('room')" value="{{ old('room', $schedule->room) }}" placeholder="contoh: R-101" />
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Perbarui Jadwal</x-button>
                <a href="{{ route('admin.schedules.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection

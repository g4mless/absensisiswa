@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Input Absensi</h1>
            <p class="text-gray-500">Pilih sesi untuk mengisi absensi</p>
        </div>
    </div>

    <x-card>
        <x-slot name="header">Pilih Sesi</x-slot>
        <x-slot name="subtitle">{{ \Carbon\Carbon::parse($selectedDate ?? date('Y-m-d'))->translatedFormat('l, d F Y') }}</x-slot>

        <form method="GET" action="{{ route('teacher.attendance') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <x-select label="Tanggal" name="date" :options="$dateOptions ?? []" :value="$selectedDate ?? date('Y-m-d')" />
            </div>
            <div class="flex items-end">
                <x-button type="submit" variant="primary">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    Tampilkan Sesi
                </x-button>
            </div>
        </form>
    </x-card>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($sessions ?? [] as $session)
            <x-card>
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-bold text-gray-900">{{ $session->subject->name ?? '-' }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $session->class->name ?? $session->classroom->name ?? '-' }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }}</p>
                    </div>
                    <x-badge variant="{{ $session->attendance_completed ? 'success' : 'warning' }}">
                        {{ $session->attendance_completed ? 'Selesai' : 'Belum' }}
                    </x-badge>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('teacher.attendance.show', ['sessionId' => $session->id, 'date' => $selectedDate ?? date('Y-m-d')]) }}">
                        <x-button variant="primary" size="sm" class="w-full">Isi Absensi</x-button>
                    </a>
                </div>
            </x-card>
        @empty
            <div class="sm:col-span-2 lg:col-span-3">
                <x-card>
                    <x-empty-state title="Tidak ada sesi" description="Tidak ditemukan sesi mengajar pada tanggal ini." />
                </x-card>
            </div>
        @endforelse
    </div>
</div>
@endsection

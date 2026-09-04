@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kelas Saya</h1>
            <p class="text-gray-500">Daftar kelas yang Anda ajar</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($classes ?? [] as $class)
            <x-card>
                <div class="flex items-start justify-between">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-bold text-gray-900">{{ $class->name }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $class->major?->code ?? '-' }}</p>
                    </div>
                    <x-badge variant="info">{{ $class->students_count ?? 0 }} siswa</x-badge>
                </div>

                <div class="mt-4 space-y-2">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342"/></svg>
                        <span>{{ $class->homeroom_teacher ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        <span>{{ $class->schedule_count ?? 0 }} sesi/minggu</span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('teacher.classes.show', $class->id) }}">
                        <x-button variant="ghost" size="sm" class="w-full">Lihat Detail</x-button>
                    </a>
                </div>
            </x-card>
        @empty
            <div class="sm:col-span-2 lg:col-span-3">
                <x-card>
                    <x-empty-state title="Belum ada kelas" description="Anda belum ditugaskan mengajar kelas manapun." />
                </x-card>
            </div>
        @endforelse
    </div>
</div>
@endsection

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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <x-card>
                <x-slot name="header">Pilih Sesi</x-slot>

                <form method="GET" action="{{ route('teacher.attendance') }}" class="space-y-4">
                    <x-select label="Tanggal" name="date" :options="$dateOptions ?? []" :value="$selectedDate ?? date('Y-m-d')" />

                    <x-button type="submit" variant="primary" class="w-full">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        Tampilkan Sesi
                    </x-button>
                </form>

                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                    @forelse($sessions ?? [] as $session)
                        <a href="{{ route('teacher.attendance.show', $session->id) }}"
                           class="block rounded-xl border {{ $selectedSession == $session->id ? 'border-primary-300 bg-primary-50' : 'border-gray-200 hover:border-primary-200 hover:bg-gray-50' }} p-4 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $session->subject->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $session->classroom->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $session->start_time }} - {{ $session->end_time }}</p>
                                </div>
                                <x-badge variant="{{ $session->attendance_completed ? 'success' : 'warning' }}">
                                    {{ $session->attendance_completed ? 'Selesai' : 'Belum' }}
                                </x-badge>
                            </div>
                        </a>
                    @empty
                        <x-empty-state title="Tidak ada sesi" description="Tidak ditemukan sesi mengajar pada tanggal ini." />
                    @endforelse
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-2">
            @if(isset($selectedSessionData))
                <x-card>
                    <x-slot name="header">{{ $selectedSessionData->subject->name ?? '-' }}</x-slot>
                    <x-slot name="subtitle">{{ $selectedSessionData->classroom->name ?? '-' }} &middot; {{ $selectedSessionData->start_time }} - {{ $selectedSessionData->end_time }}</x-slot>

                    <form method="POST" action="{{ route('teacher.attendance.update', $selectedSessionData->id) }}">
                        @csrf
                        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex gap-2">
                                <x-button type="button" variant="success" size="sm" x-on:click="markAll('HADIR')">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    Semua Hadir
                                </x-button>
                                <x-button type="button" variant="danger" size="sm" x-on:click="markAll('ALFA')">
                                    Semua Alfa
                                </x-button>
                            </div>
                            <x-button type="submit" variant="primary" size="sm">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Simpan Absensi
                            </x-button>
                        </div>

                        <div class="overflow-x-auto">
                            <x-table>
                                <thead>
                                    <tr>
                                        <th class="w-12">No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody x-data="{ statuses: {} }">
                                    @forelse($selectedSessionData->students ?? [] as $index => $student)
                                        <tr>
                                            <td class="text-gray-500">{{ $index + 1 }}</td>
                                            <td class="font-mono text-sm">{{ $student->nis ?? '-' }}</td>
                                            <td class="font-medium">{{ $student->name ?? '-' }}</td>
                                            <td>
                                                <div class="flex items-center justify-center gap-1 sm:gap-2">
                                                    @foreach(['HADIR', 'IZIN', 'SAKIT', 'ALFA'] as $status)
                                                        <label class="inline-flex items-center gap-1 cursor-pointer">
                                                            <input type="radio"
                                                                name="attendance[{{ $student->id }}]"
                                                                value="{{ $status }}"
                                                                x-model="statuses[{{ $student->id }}]"
                                                                {{ ($student->pivot->status ?? '') === $status ? 'checked' : '' }}
                                                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300" />
                                                            <span class="text-xs {{ match($status) { 'HADIR' => 'text-green-600', 'IZIN' => 'text-yellow-600', 'SAKIT' => 'text-blue-600', 'ALFA' => 'text-red-600', default => 'text-gray-600' } }}">
                                                                {{ substr($status, 0, 1) }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">
                                                <x-empty-state title="Tidak ada siswa" description="Tidak ada siswa di kelas ini." />
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </x-table>
                        </div>
                    </form>
                </x-card>
            @else
                <x-card>
                    <x-empty-state title="Pilih sesi" description="Pilih sesi mengajar dari panel kiri untuk mengisi absensi." />
                </x-card>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function markAll(status) {
        document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(radio => {
            radio.checked = true;
        });
    }
</script>
@endpush
@endsection

@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('teacher.attendance') }}" class="text-sm text-gray-500 hover:text-primary-600 transition-colors">Absensi</a>
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                <span class="text-sm font-medium text-gray-900">Sesi</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $session->subject->name ?? '-' }}</h1>
            <p class="text-gray-500">{{ $session->classroom->name ?? '-' }} &middot; {{ $session->start_time }} - {{ $session->end_time }}</p>
        </div>
        <div class="flex gap-2">
            @if($session->attendance_completed)
                <x-badge variant="success" class="text-sm px-3 py-1.5">
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    Absensi Selesai
                </x-badge>
            @else
                <x-badge variant="warning" class="text-sm px-3 py-1.5">Belum Diisi</x-badge>
            @endif
        </div>
    </div>

    @if($errors->any())
        <x-alert variant="danger" title="Galat" dismissible>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3">
            <x-card>
                <x-slot name="header">Daftar Kehadiran</x-slot>
                <x-slot name="subtitle">{{ count($session->students ?? []) }} siswa</x-slot>

                <form method="POST" action="{{ route('teacher.attendance.update', $session->id) }}">
                    @csrf

                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex gap-2">
                            <x-button type="button" variant="success" size="sm" onclick="markAll('HADIR')">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Semua Hadir
                            </x-button>
                            <x-button type="button" variant="danger" size="sm" onclick="markAll('ALFA')">
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
                                    <th class="text-center min-w-[280px]">Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($session->students ?? [] as $index => $student)
                                    @php
                                        $currentStatus = $student->pivot->status ?? 'HADIR';
                                    @endphp
                                    <tr>
                                        <td class="text-gray-500">{{ $index + 1 }}</td>
                                        <td class="font-mono text-sm">{{ $student->nis ?? '-' }}</td>
                                        <td class="font-medium">{{ $student->name ?? '-' }}</td>
                                        <td>
                                            <div class="flex items-center justify-center gap-2 sm:gap-3">
                                                @foreach(['HADIR' => 'H', 'IZIN' => 'I', 'SAKIT' => 'S', 'ALFA' => 'A'] as $status => $abbr)
                                                    <label class="inline-flex items-center gap-1.5 cursor-pointer group" title="{{ $status }}">
                                                        <input type="radio"
                                                            name="attendance[{{ $student->id }}]"
                                                            value="{{ $status }}"
                                                            {{ $currentStatus === $status ? 'checked' : '' }}
                                                            class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-500" />
                                                        <span class="text-xs font-semibold {{ match($status) {
                                                            'HADIR' => 'text-green-600 group-hover:text-green-700',
                                                            'IZIN' => 'text-yellow-600 group-hover:text-yellow-700',
                                                            'SAKIT' => 'text-blue-600 group-hover:text-blue-700',
                                                            'ALFA' => 'text-red-600 group-hover:text-red-700',
                                                            default => 'text-gray-600'
                                                        } }}">{{ $abbr }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <x-empty-state title="Tidak ada siswa" description="Tidak ada siswa yang terdaftar di sesi ini." />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </x-table>
                    </div>
                </form>
            </x-card>
        </div>

        <div class="lg:col-span-1">
            <x-card>
                <x-slot name="header">Info Sesi</x-slot>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Mata Pelajaran</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $session->subject->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Kelas</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $session->classroom->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Ruangan</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $session->room ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Waktu</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $session->start_time }} - {{ $session->end_time }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total Siswa</p>
                        <p class="text-sm font-semibold text-gray-900">{{ count($session->students ?? []) }}</p>
                    </div>

                    @php
                        $attendanceStats = ['HADIR' => 0, 'IZIN' => 0, 'SAKIT' => 0, 'ALFA' => 0];
                        foreach($session->students ?? [] as $s) {
                            $status = $s->pivot->status ?? 'HADIR';
                            $attendanceStats[$status] = ($attendanceStats[$status] ?? 0) + 1;
                        }
                    @endphp

                    <div class="pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-500 mb-2">Ringkasan</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-green-50 p-2 text-center">
                                <p class="text-lg font-bold text-green-700">{{ $attendanceStats['HADIR'] }}</p>
                                <p class="text-[10px] text-green-600">Hadir</p>
                            </div>
                            <div class="rounded-lg bg-yellow-50 p-2 text-center">
                                <p class="text-lg font-bold text-yellow-700">{{ $attendanceStats['IZIN'] }}</p>
                                <p class="text-[10px] text-yellow-600">Izin</p>
                            </div>
                            <div class="rounded-lg bg-blue-50 p-2 text-center">
                                <p class="text-lg font-bold text-blue-700">{{ $attendanceStats['SAKIT'] }}</p>
                                <p class="text-[10px] text-blue-600">Sakit</p>
                            </div>
                            <div class="rounded-lg bg-red-50 p-2 text-center">
                                <p class="text-lg font-bold text-red-700">{{ $attendanceStats['ALFA'] }}</p>
                                <p class="text-[10px] text-red-600">Alfa</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
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

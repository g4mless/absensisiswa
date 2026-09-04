@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-duty-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Absensi Hari Ini</h1>
            <p class="text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('duty-teacher.attendance.all') }}">
                <x-button variant="secondary" size="sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    Semua Absensi
                </x-button>
            </a>
        </div>
    </div>

    <x-card>
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4">
            <div class="flex-1">
                <x-search-input name="search" placeholder="Cari siswa..." value="{{ request('search') }}" />
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach(['' => 'Semua', 'not_present' => 'Tidak Hadir', 'excused' => 'Berizin', 'sick' => 'Sakit', 'absent' => 'Alfa', 'no_attendance' => 'Belum Absen'] as $value => $label)
                    <a href="{{ route('duty-teacher.attendance.today', array_merge(request()->query(), ['filter' => $value])) }}"
                       class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-medium transition-colors {{ (request('filter', '') === $value) ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <x-table>
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Sesi</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayAttendance ?? [] as $index => $record)
                    <tr>
                        <td class="text-gray-500">{{ $index + 1 }}</td>
                        <td>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $record->student->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $record->student->nis ?? '-' }}</p>
                            </div>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600">{{ $record->student->classroom->name ?? '-' }}</td>
                        <td class="text-sm text-gray-600">
                            <div>
                                <p class="font-medium">{{ $record->session->subject->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $record->session->start_time ?? '' }} - {{ $record->session->end_time ?? '' }}</p>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($record->status)
                                <x-badge variant="{{ match($record->status) { 'HADIR' => 'success', 'IZIN' => 'warning', 'SAKIT' => 'info', 'ALFA' => 'danger', default => 'neutral' } }}">
                                    {{ $record->status }}
                                </x-badge>
                            @else
                                <x-badge variant="neutral">Belum Absen</x-badge>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-primary-600 transition-colors" title="Detail Siswa">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                </button>
                                <button class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-primary-600 transition-colors" title="Hubungi Orang Tua">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-empty-state title="Tidak ada data" description="Tidak ditemukan data absensi untuk filter ini." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        @if(isset($todayAttendance) && $todayAttendance instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                <x-pagination :paginator="$todayAttendance" />
            </div>
        @endif
    </x-card>
</div>
@endsection

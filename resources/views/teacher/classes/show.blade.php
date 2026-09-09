@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <div class="min-w-0">
            <div class="mb-1 flex items-center gap-2">
                <a href="{{ route('teacher.classes') }}" class="inline-flex min-h-[44px] items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">Kelas</a>
                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                <span class="truncate text-sm font-medium text-gray-900">{{ $class->name ?? '-' }}</span>
            </div>
            <h1 class="truncate text-xl font-bold text-gray-900 sm:text-2xl">{{ $class->name ?? '-' }}</h1>
            <p class="mt-0.5 truncate text-sm text-gray-500">{{ $class->major?->code ?? '' }} &middot; {{ $class->students_count ?? 0 }} siswa</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('teacher.attendance') }}?class={{ $class->id }}" class="w-full sm:w-auto">
                <x-button variant="primary" size="sm" class="min-h-[44px] w-full sm:w-auto">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    Absensi
                </x-button>
            </a>
        </div>
    </div>

    <x-card>
        <x-slot name="header">Daftar Siswa</x-slot>
        <x-slot name="subtitle">{{ $class->name }} - Total {{ $class->students_count ?? 0 }} siswa</x-slot>

        <div class="mb-4">
            <x-search-input name="search" placeholder="Cari siswa..." />
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-2 md:hidden">
            @forelse($students ?? [] as $index => $student)
                <a href="{{ route('teacher.students.show', $student->id) }}" class="block rounded-xl border border-gray-100 bg-white p-3 shadow-sm active:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-xs font-bold text-primary-700">{{ substr($student->name ?? '?', 0, 1) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-900">{{ $student->name ?? '-' }}</p>
                            <p class="font-mono text-xs text-gray-500">{{ $student->nis ?? '-' }}</p>
                        </div>
                        <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </div>
                    <div class="mt-2.5 grid grid-cols-4 gap-1.5 text-center">
                        <span class="rounded-lg bg-green-50 px-1 py-1.5 text-xs font-bold text-green-700">{{ $student->attendance_summary['present'] ?? 0 }}<span class="block text-[9px] font-medium">Hadir</span></span>
                        <span class="rounded-lg bg-yellow-50 px-1 py-1.5 text-xs font-bold text-yellow-700">{{ $student->attendance_summary['excused'] ?? 0 }}<span class="block text-[9px] font-medium">Izin</span></span>
                        <span class="rounded-lg bg-blue-50 px-1 py-1.5 text-xs font-bold text-blue-700">{{ $student->attendance_summary['sick'] ?? 0 }}<span class="block text-[9px] font-medium">Sakit</span></span>
                        <span class="rounded-lg bg-red-50 px-1 py-1.5 text-xs font-bold text-red-700">{{ $student->attendance_summary['absent'] ?? 0 }}<span class="block text-[9px] font-medium">Alfa</span></span>
                    </div>
                </a>
            @empty
                <x-empty-state title="Belum ada siswa" description="Kelas ini belum memiliki siswa terdaftar." />
            @endforelse
        </div>

        <div class="hidden md:block">
        <x-table>
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th class="text-center">Hadir</th>
                    <th class="text-center">Izin</th>
                    <th class="text-center">Sakit</th>
                    <th class="text-center">Alfa</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students ?? [] as $index => $student)
                    <tr>
                        <td class="text-gray-500">{{ $index + 1 }}</td>
                        <td class="font-mono text-sm">{{ $student->nis ?? '-' }}</td>
                        <td class="font-medium">{{ $student->name ?? '-' }}</td>
                        <td class="text-center">
                            <span class="text-sm font-semibold text-green-600">{{ $student->attendance_summary['present'] ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-sm font-semibold text-yellow-600">{{ $student->attendance_summary['excused'] ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-sm font-semibold text-blue-600">{{ $student->attendance_summary['sick'] ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-sm font-semibold text-red-600">{{ $student->attendance_summary['absent'] ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <x-dropdown>
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z"/></svg>
                                    </button>
                                </x-slot>
                                <a href="{{ route('teacher.students.show', $student->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    Lihat Detail
                                </a>
                                <a href="{{ route('teacher.students.show', $student->id) }}?tab=attendance" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                    Riwayat Absensi
                                </a>
                            </x-dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-empty-state title="Belum ada siswa" description="Kelas ini belum memiliki siswa terdaftar." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
        </div>

        @if(isset($students) && $students instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                <x-pagination :paginator="$students" />
            </div>
        @endif
    </x-card>
</div>
@endsection

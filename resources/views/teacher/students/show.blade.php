@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 mb-1">
        <a href="{{ route('teacher.classes') }}" class="text-sm text-gray-500 hover:text-primary-600 transition-colors">Kelas</a>
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <a href="{{ route('teacher.classes.show', $student->classroom->id ?? 0) }}" class="text-sm text-gray-500 hover:text-primary-600 transition-colors">{{ $student->classroom->name ?? '-' }}</a>
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <span class="text-sm font-medium text-gray-900">{{ $student->name ?? '-' }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <x-card>
                <div class="text-center">
                    <div class="mx-auto h-20 w-20 rounded-full bg-primary-100 flex items-center justify-center text-2xl font-bold text-primary-700">
                        {{ substr($student->name ?? 'S', 0, 1) }}
                    </div>
                    <h2 class="mt-3 text-lg font-bold text-gray-900">{{ $student->name ?? '-' }}</h2>
                    <p class="text-sm text-gray-500">{{ $student->classroom->name ?? '-' }}</p>
                </div>

                <div class="mt-6 space-y-3">
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                        <span class="text-sm text-gray-500">NIS</span>
                        <span class="text-sm font-semibold text-gray-900 font-mono">{{ $student->nis ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                        <span class="text-sm text-gray-500">NISN</span>
                        <span class="text-sm font-semibold text-gray-900 font-mono">{{ $student->nisn ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                        <span class="text-sm text-gray-500">Kelas</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $student->classroom->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                        <span class="text-sm text-gray-500">Jenis Kelamin</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $student->gender === 'M' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                    @if($student->phone)
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                        <span class="text-sm text-gray-500">Telepon</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $student->phone }}</span>
                    </div>
                    @endif
                    @if($student->address)
                    <div class="rounded-lg bg-gray-50 px-4 py-2.5">
                        <span class="text-sm text-gray-500 block mb-1">Alamat</span>
                        <span class="text-sm text-gray-900">{{ $student->address }}</span>
                    </div>
                    @endif
                </div>
            </x-card>

            <x-card class="mt-6">
                <x-slot name="header">Statistik Kehadiran</x-slot>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-green-50 p-3 text-center">
                        <p class="text-2xl font-bold text-green-700">{{ $student->attendance_summary['present'] ?? 0 }}</p>
                        <p class="text-xs text-green-600">Hadir</p>
                    </div>
                    <div class="rounded-lg bg-yellow-50 p-3 text-center">
                        <p class="text-2xl font-bold text-yellow-700">{{ $student->attendance_summary['excused'] ?? 0 }}</p>
                        <p class="text-xs text-yellow-600">Izin</p>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-3 text-center">
                        <p class="text-2xl font-bold text-blue-700">{{ $student->attendance_summary['sick'] ?? 0 }}</p>
                        <p class="text-xs text-blue-600">Sakit</p>
                    </div>
                    <div class="rounded-lg bg-red-50 p-3 text-center">
                        <p class="text-2xl font-bold text-red-700">{{ $student->attendance_summary['absent'] ?? 0 }}</p>
                        <p class="text-xs text-red-600">Alfa</p>
                    </div>
                </div>
                <div class="mt-3 rounded-lg bg-gray-50 p-3 text-center">
                    <p class="text-lg font-bold text-gray-900">{{ $student->attendance_summary['rate'] ?? '0%' }}</p>
                    <p class="text-xs text-gray-500">Tingkat Kehadiran</p>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <x-slot name="header">Riwayat Kehadiran</x-slot>
                <x-slot name="subtitle">10 catatan terakhir</x-slot>

                <x-table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th class="text-center">Status</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($student->attendance_history ?? [] as $record)
                            <tr>
                                <td class="text-sm">{{ $record->date ? \Carbon\Carbon::parse($record->date)->format('d M Y') : '-' }}</td>
                                <td class="text-sm">{{ $record->session->subject->name ?? '-' }}</td>
                                <td class="text-sm">{{ $record->session->classroom->name ?? '-' }}</td>
                                <td class="text-center">
                                    <x-badge variant="{{ match($record->status) { 'HADIR' => 'success', 'IZIN' => 'warning', 'SAKIT' => 'info', 'ALFA' => 'danger', default => 'neutral' } }}">
                                        {{ $record->status }}
                                    </x-badge>
                                </td>
                                <td class="text-sm text-gray-500">{{ $record->time ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state title="Belum ada riwayat" description="Belum ada catatan kehadiran." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>

            <x-card>
                <x-slot name="header">Surat Izin</x-slot>

                @forelse($student->excuses ?? [] as $excuse)
                    <div class="flex items-start gap-4 rounded-xl border border-gray-100 p-4 {{ !$excuse->is_read ? 'bg-primary-50/50 border-primary-200' : '' }}">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ match($excuse->status) { 'pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-700' } }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900">{{ $excuse->type ?? '-' }}</p>
                                <x-badge variant="{{ $excuse->status === 'pending' ? 'warning' : ($excuse->status === 'approved' ? 'success' : 'danger') }}">
                                    {{ ucfirst($excuse->status) }}
                                </x-badge>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $excuse->date ? \Carbon\Carbon::parse($excuse->date)->format('d M Y') : '-' }}</p>
                            @if($excuse->description)
                                <p class="text-sm text-gray-600 mt-2">{{ Str::limit($excuse->description, 120) }}</p>
                            @endif
                            @if($excuse->file_path)
                                <a href="{{ asset('storage/' . $excuse->file_path) }}" target="_blank" class="mt-2 inline-flex items-center gap-1 text-xs text-primary-600 hover:text-primary-700">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    Lihat Surat
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-empty-state title="Tidak ada surat izin" description="Siswa ini belum memiliki surat izin." />
                @endforelse
            </x-card>
        </div>
    </div>
</div>
@endsection

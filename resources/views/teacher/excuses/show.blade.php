@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar whitespace-nowrap">
        <a href="{{ route('teacher.excuses') }}" class="inline-flex min-h-[44px] items-center shrink-0 text-sm text-gray-500 hover:text-primary-600 transition-colors">Surat Izin</a>
        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <span class="text-sm font-medium text-gray-900">Detail</span>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <div class="min-w-0">
            <h1 class="truncate text-xl font-bold text-gray-900 sm:text-2xl">Detail Surat Izin</h1>
            <p class="mt-0.5 truncate text-sm text-gray-500">{{ $excuse->student->name ?? '-' }} &middot; {{ $excuse->date ? \Carbon\Carbon::parse($excuse->date)->format('d M Y') : '-' }}</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700">
            Terkirim
        </span>
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

    <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            <x-card>
                <x-slot name="header">Informasi Surat</x-slot>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <p class="text-xs text-gray-500">Siswa</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $excuse->student->name ?? '-' }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <p class="text-xs text-gray-500">Kelas</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $excuse->student->class->name ?? $excuse->student->classroom->name ?? '-' }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <p class="text-xs text-gray-500">Tanggal Izin</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $excuse->date ? \Carbon\Carbon::parse($excuse->date)->format('d F Y') : '-' }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-4 py-3">
                            <p class="text-xs text-gray-500">Jenis Izin</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $excuse->type ?? '-' }}</p>
                        </div>
                    </div>

                    @if($excuse->reason ?? $excuse->description ?? null)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Keterangan</p>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p class="text-sm text-gray-700">{{ $excuse->reason ?? $excuse->description }}</p>
                        </div>
                    </div>
                    @endif

                    @if($excuse->file_path)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Lampiran</p>
                        <div class="rounded-lg border border-gray-200 p-4">
                            @if(in_array(pathinfo($excuse->file_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                                <img src="{{ asset('storage/' . $excuse->file_path) }}" alt="Surat Izin" class="max-w-full rounded-lg" />
                            @else
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100">
                                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ basename($excuse->file_path) }}</p>
                                        <p class="text-xs text-gray-500">Dokumen file</p>
                                    </div>
                                    <a href="{{ asset('storage/' . $excuse->file_path) }}" target="_blank" class="shrink-0">
                                        <x-button variant="secondary" size="sm">
                                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                            Unduh
                                        </x-button>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-1">
            <x-card>
                <x-slot name="header">Informasi Pengiriman</x-slot>

                <div class="text-center py-4">
                    <div class="mx-auto h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-blue-700">
                        Surat Terkirim ke Server
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Dikirim oleh guru</p>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Tanggal Izin</span>
                            <span class="text-gray-900">{{ $excuse->date ? \Carbon\Carbon::parse($excuse->date)->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Dikirim</span>
                            <span class="text-gray-900">{{ $excuse->created_at ? \Carbon\Carbon::parse($excuse->created_at)->format('d M Y, H:i') : '-' }}</span>
                        </div>
                        @if($excuse->updated_at && $excuse->updated_at != $excuse->created_at)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Diperbarui</span>
                            <span class="text-gray-900">{{ \Carbon\Carbon::parse($excuse->updated_at)->format('d M Y, H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

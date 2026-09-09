@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">Surat Izin</h1>
            <p class="mt-0.5 text-sm text-gray-500">Daftar surat izin yang dikirim ke server</p>
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

    <x-card>
        <div class="mb-4">
            <x-search-input name="search" placeholder="Cari siswa atau surat izin..." value="{{ request('search') }}" />
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-2 md:hidden">
            @forelse($excuses ?? [] as $excuse)
                <a href="{{ route('teacher.excuses.show', $excuse->id) }}" class="block rounded-xl border border-gray-100 bg-white p-3 shadow-sm active:bg-gray-50">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-900">{{ $excuse->student->name ?? '-' }}</p>
                            <p class="mt-0.5 truncate text-xs text-gray-500">{{ $excuse->student->class->name ?? $excuse->student->classroom->name ?? '-' }} &middot; {{ $excuse->date ? \Carbon\Carbon::parse($excuse->date)->format('d M Y') : '-' }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $excuse->type ?? '-' }}</p>
                        </div>
                        <svg class="h-4 w-4 shrink-0 text-gray-400 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </div>
                </a>
            @empty
                <x-empty-state title="Tidak ada surat izin" description="Belum ada surat izin dari siswa." />
            @endforelse
        </div>

        <div class="hidden md:block">
        <x-table>
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($excuses ?? [] as $excuse)
                    <tr>
                        <td>
                            <span class="text-sm font-medium text-gray-900">{{ $excuse->student->name ?? '-' }}</span>
                        </td>
                        <td class="text-sm text-gray-600">{{ $excuse->student->class->name ?? $excuse->student->classroom->name ?? '-' }}</td>
                        <td class="text-sm text-gray-600">{{ $excuse->date ? \Carbon\Carbon::parse($excuse->date)->format('d M Y') : '-' }}</td>
                        <td class="text-sm text-gray-600">{{ $excuse->type ?? '-' }}</td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('teacher.excuses.show', $excuse->id) }}" class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-primary-600 transition-colors" title="Lihat Detail">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-empty-state title="Tidak ada surat izin" description="Belum ada surat izin dari siswa." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
        </div>

        @if(isset($excuses) && $excuses instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                <x-pagination :paginator="$excuses" />
            </div>
        @endif
    </x-card>
</div>


@endsection

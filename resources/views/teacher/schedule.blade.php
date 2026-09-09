@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">Jadwal Mengajar</h1>
        <p class="text-sm text-gray-500">Jadwal mingguan Anda</p>
    </div>

    {{-- Mobile: stacked per-day cards --}}
    <div class="space-y-3 lg:hidden">
        @php
            $days = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat'];
            $todayName = now()->translatedFormat('l');
        @endphp
        @forelse($days as $dayKey => $dayLabel)
            @php
                $daySessions = collect($timeSlots ?? [])->map(fn($slot) => ['slot' => $slot, 'session' => $schedule[$dayKey][$slot->id] ?? null])->filter(fn($r) => $r['session'])->values();
                $isToday = $todayName === $dayLabel;
            @endphp
            <x-card>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900">{{ $dayLabel }}</h3>
                    @if($isToday)
                        <x-badge variant="success">Hari ini</x-badge>
                    @else
                        <span class="text-xs text-gray-400">{{ $daySessions->count() }} sesi</span>
                    @endif
                </div>
                <div class="space-y-2">
                    @forelse($daySessions as $row)
                        <div class="flex items-center gap-3 rounded-lg border {{ $isToday ? 'border-primary-200 bg-primary-50/40' : 'border-gray-100' }} p-2.5">
                            <div class="shrink-0 rounded-md bg-white px-2 py-1 text-center shadow-sm ring-1 ring-gray-100">
                                <p class="text-[11px] font-bold leading-tight text-gray-900">{{ substr($row['slot']->start_time, 0, 5) }}</p>
                                <p class="text-[10px] leading-tight text-gray-400">{{ substr($row['slot']->end_time, 0, 5) }}</p>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900">{{ $row['session']->subject->name ?? '-' }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $row['session']->class->name ?? $row['session']->classroom->name ?? '-' }} &middot; R. {{ $row['session']->room ?? '-' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-200 py-3 text-center text-xs text-gray-400">Tidak ada sesi</p>
                    @endforelse
                </div>
            </x-card>
        @empty
            <x-card><x-empty-state title="Tidak ada jadwal" description="Jadwal mengajar belum tersedia." /></x-card>
        @endforelse
    </div>

    <x-card class="hidden lg:block">
        <div class="overflow-x-auto -mx-6 px-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">Waktu</th>
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider {{ now()->translatedFormat('l') === $day ? 'bg-primary-50 text-primary-700' : '' }}">
                                {{ $day }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($timeSlots ?? [] as $slot)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-700 whitespace-nowrap">
                                {{ $slot->start_time }}<br>
                                <span class="text-gray-400">-</span><br>
                                {{ $slot->end_time }}
                            </td>
                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $dayKey)
                                @php
                                    $session = $schedule[$dayKey][$slot->id] ?? null;
                                    $isToday = now()->translatedFormat('l') === match($dayKey) {
                                        'monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu',
                                        'thursday' => 'Kamis', 'friday' => 'Jumat'
                                    };
                                @endphp
                                <td class="px-2 py-2 {{ $isToday ? 'bg-primary-50/50' : '' }}">
                                    @if($session)
                                        <div class="rounded-lg border border-primary-200 bg-white p-2.5 shadow-sm hover:shadow-md transition-shadow">
                                            <p class="text-xs font-bold text-primary-700">{{ $session->subject->name ?? '-' }}</p>
                                            <p class="text-[11px] text-gray-600 mt-0.5">{{ $session->class->name ?? $session->classroom->name ?? '-' }}</p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">R. {{ $session->room ?? '-' }}</p>
                                        </div>
                                    @else
                                        <div class="rounded-lg border border-dashed border-gray-200 p-2.5 text-center">
                                            <span class="text-xs text-gray-300">-</span>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <x-empty-state title="Tidak ada jadwal" description="Jadwal mengajar belum tersedia." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection

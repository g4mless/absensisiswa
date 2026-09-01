@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Jadwal Mengajar</h1>
        <p class="text-gray-500">Jadwal mingguan Anda</p>
    </div>

    <x-card>
        <div class="overflow-x-auto">
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
                                            <p class="text-[11px] text-gray-600 mt-0.5">{{ $session->classroom->name ?? '-' }}</p>
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

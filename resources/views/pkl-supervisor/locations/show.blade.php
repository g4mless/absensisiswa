@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-pkl-supervisor')
@endsection

@section('content')
@php
    // Kunci channel: student_pkl.id bila ada, fallback s{student.id} (lihat PklLocationUpdated).
    // Null-safe: controller mungkin belum mengirim $student (stubs) — halaman tetap render.
    $s = $student ?? null;
    $lastLoc = ($s?->lastLocation) ?? ($locations ?? collect())->first() ?? null;
    $channelKey = ($s?->studentPklActiveId)
        ?? ($lastLoc->student_pkl_id ?? null)
        ?? ($s ? 's'.$s->id : '');
    $lastRecordedAt = ($lastLoc->recorded_at ?? null) ?? ($lastLoc->created_at ?? null);
@endphp
<div class="space-y-6" x-data="supervisorDetail(@json((string) $channelKey))" x-init="init()">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('pkl-supervisor.locations') }}" class="inline-flex min-h-[48px] min-w-[48px] items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Lokasi: {{ ($student ?? null)?->user?->name ?? '-' }}</h1>
                <p class="text-gray-500">Tracking GPS real-time</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span x-show="realtime" class="md-badge md-badge-success">LIVE WEBSOCKET</span>
            <span x-show="!realtime" class="md-badge md-badge-warning">POLLING 30 dtk</span>
            <span x-show="isStale" class="md-badge md-badge-warning">STALE &gt; 2 mnt</span>
            <span x-show="!isStale && hasFix" class="md-badge md-badge-success">LIVE</span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Real-time GPS Display --}}
        <div class="space-y-6 lg:col-span-1">
            <x-card elevated>
                <x-slot name="header">Lokasi Terkini</x-slot>
                <div class="flex flex-col items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-100">
                        <svg class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0Z"/>
                        </svg>
                    </div>

                    <div class="w-full space-y-3">
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Latitude</p>
                            <p class="text-xl font-bold text-gray-900" data-live="lat">{{ $lastLoc->latitude ?? '---' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Longitude</p>
                            <p class="text-xl font-bold text-gray-900" data-live="lng">{{ $lastLoc->longitude ?? '---' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Akurasi</p>
                            <p class="text-base font-medium text-gray-700" data-live="acc">{{ isset($lastLoc->accuracy) ? number_format($lastLoc->accuracy, 1).' m' : '-' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Terakhir Update</p>
                            <p class="text-base font-medium text-gray-700" data-live="time">{{ $lastRecordedAt ? \Carbon\Carbon::parse($lastRecordedAt)->diffForHumans() : '-' }}</p>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Student Info --}}
            <x-card>
                <x-slot name="header">Info Siswa</x-slot>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">NIS</span>
                        <span class="text-sm font-medium text-gray-900">{{ ($student ?? null)?->nis ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-2">
                        <span class="text-sm text-gray-500">Perusahaan</span>
                        <span class="text-sm font-medium text-gray-900">{{ ($student ?? null)?->pkl->company ?? ($student ?? null)?->pkl->tempat_pkl ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-500">Hari Ini</span>
                        @if(($student ?? null)?->todayAttendance ?? null)
                            <x-badge variant="success">HADIR</x-badge>
                        @else
                            <x-badge variant="warning">BELUM ABSEN</x-badge>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>

        {{-- History (live-prepend) --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Location History --}}
            <x-card>
                <x-slot name="header">Riwayat Lokasi</x-slot>
                <x-slot name="subtitle">Lokasi yang dikirim hari ini — baris baru muncul otomatis via WebSocket</x-slot>

                <div class="overflow-x-auto">
                <x-table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Akurasi</th>
                        </tr>
                    </thead>
                    <tbody data-live="history">
                        @forelse($locations ?? [] as $loc)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($loc->recorded_at ?? $loc->created_at)->format('H:i:s') }}</td>
                                <td class="text-sm">{{ $loc->latitude }}</td>
                                <td class="text-sm">{{ $loc->longitude }}</td>
                                <td class="text-sm">{{ $loc->accuracy ? number_format($loc->accuracy, 1) . 'm' : '-' }}</td>
                            </tr>
                        @empty
                            <tr data-live="empty">
                                <td colspan="4">
                                    <x-empty-state title="Belum ada riwayat" description="Siswa ini belum mengirim lokasi hari ini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>
                </div>

                @if(($locations ?? collect())->hasPages())
                    <div class="mt-4">
                        <x-pagination :paginator="$locations" />
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>

@push('scripts')
<script>
function supervisorDetail(channelKey) {
    return {
        channelKey: channelKey || '',
        realtime: false,
        hasFix: @json((bool) $lastRecordedAt),
        isStale: @json($lastRecordedAt ? \Carbon\Carbon::parse($lastRecordedAt)->lt(now()->subMinutes(2)) : true),
        lastTs: @json($lastRecordedAt ? \Carbon\Carbon::parse($lastRecordedAt)->timestamp * 1000 : null),
        fallbackTimer: null,

        init() {
            setInterval(() => this.checkStale(), 10000);
            setTimeout(() => this.connect(), 1500);
            window.addEventListener('echo:connected', () => this.connect());
            window.addEventListener('echo:disconnected', () => this.startFallback());
        },

        connect() {
            if (!window.Echo || !this.channelKey) {
                this.startFallback();
                return;
            }
            this.realtime = true;
            if (this.fallbackTimer) {
                clearInterval(this.fallbackTimer);
                this.fallbackTimer = null;
            }
            try {
                window.Echo.channel(`pkl.student.${this.channelKey}`)
                    .listen('.pkl.location.updated', (e) => this.applyUpdate(e));
            } catch (err) {
                console.warn('[pkl] gagal subscribe', this.channelKey, err);
                this.startFallback();
            }
        },

        applyUpdate(e) {
            this.hasFix = true;
            this.isStale = false;
            this.lastTs = Date.now();
            const set = (name, value) => {
                const el = document.querySelector(`[data-live="${name}"]`);
                if (el) el.textContent = value;
            };
            set('lat', e.latitude);
            set('lng', e.longitude);
            set('acc', e.accuracy != null ? Number(e.accuracy).toFixed(1) + ' m' : '-');
            set('time', 'baru saja');
            const tbody = document.querySelector('[data-live="history"]');
            if (tbody) {
                const empty = tbody.querySelector('[data-live="empty"]');
                if (empty) empty.remove();
                const tr = document.createElement('tr');
                tr.className = 'bg-green-50';
                const esc = (v) => String(v ?? '-').replace(/</g, '&lt;');
                const time = (e.recorded_at || '').slice(11, 19) || 'baru saja';
                tr.innerHTML = `<td>${esc(time)}</td><td class="text-sm">${esc(e.latitude)}</td><td class="text-sm">${esc(e.longitude)}</td><td class="text-sm">${e.accuracy != null ? esc(Number(e.accuracy).toFixed(1)) + 'm' : '-'}</td>`;
                tbody.prepend(tr);
                setTimeout(() => tr.classList.remove('bg-green-50'), 3000);
            }
        },

        checkStale() {
            this.isStale = this.lastTs ? (Date.now() - this.lastTs > 120000) : true;
        },

        // Fallback: reload ringan tiap 30 detik bila Echo tak tersedia.
        startFallback() {
            if (window.Echo || this.fallbackTimer) return;
            this.realtime = false;
            this.fallbackTimer = setInterval(() => window.location.reload(), 30000);
        }
    };
}
</script>
@endpush
@endsection

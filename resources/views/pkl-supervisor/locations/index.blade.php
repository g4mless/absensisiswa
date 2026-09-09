@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-pkl-supervisor')
@endsection

@section('content')
<div class="space-y-6" x-data="supervisorLocations()" x-init="init()">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Lokasi Siswa PKL</h1>
            <p class="text-gray-500">Pantau lokasi GPS semua siswa PKL</p>
        </div>
        <div class="flex items-center gap-2">
            <span x-show="realtime" class="md-badge md-badge-success">LIVE WEBSOCKET</span>
            <span x-show="!realtime" class="md-badge md-badge-warning">POLLING 30 dtk</span>
        </div>
    </div>
    <p x-show="!realtime" class="text-sm text-gray-500">Realtime tidak tersedia (Reverb belum jalan) — data diperbarui otomatis tiap 30 detik.</p>

    {{-- Students Location List --}}
    <x-card>
        <x-slot name="header">Lokasi Terkini</x-slot>
        <x-slot name="subtitle">Lokasi terakhir dari setiap siswa</x-slot>

        <div class="overflow-x-auto">
        <x-table>
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Status</th>
                    <th>Terakhir Update</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentLocations ?? [] as $location)
                    @php
                        $channelKey = $location->student_pkl_id ?? ('s'.($location->student_id ?? ''));
                        $recordedAt = $location->recorded_at ?? $location->created_at ?? null;
                        $isStale = $recordedAt ? \Carbon\Carbon::parse($recordedAt)->lt(now()->subMinutes(2)) : true;
                    @endphp
                    <tr data-loc-row="{{ $channelKey }}"
                        data-channel="{{ $channelKey }}"
                        data-recorded-at="{{ $recordedAt ? \Carbon\Carbon::parse($recordedAt)->toDateTimeString() : '' }}">
                        <td>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $location->student->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $location->student->nis ?? '-' }}</p>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600" data-col="lat">{{ $location->latitude }}</td>
                        <td class="text-sm text-gray-600" data-col="lng">{{ $location->longitude }}</td>
                        <td data-col="badge">
                            @if($isStale)
                                <x-badge variant="warning">STALE</x-badge>
                            @else
                                <x-badge variant="success">LIVE</x-badge>
                            @endif
                        </td>
                        <td class="text-sm text-gray-500" data-col="time">{{ $recordedAt ? \Carbon\Carbon::parse($recordedAt)->diffForHumans() : '-' }}</td>
                        <td>
                            <a href="{{ route('pkl-supervisor.locations.show', $location->student_id) }}">
                                <x-button variant="ghost" size="sm">Detail</x-button>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-empty-state title="Tidak ada data" description="Belum ada siswa yang mengirim lokasi GPS." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
        </div>
    </x-card>

    {{-- Alerts for students outside radius --}}
    @if(($outsideRadius ?? collect())->isNotEmpty())
        <x-card>
            <x-slot name="header">Peringatan Luar Radius</x-slot>
            <x-slot name="subtitle">Siswa yang berada di luar area PKL</x-slot>

            <div class="space-y-3">
                @foreach($outsideRadius as $alert)
                    <div class="flex items-center gap-3 rounded-lg border border-red-100 bg-red-50 p-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-red-800">{{ $alert->student->user->name ?? '-' }}</p>
                            <p class="text-xs text-red-600">{{ $alert->message }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif
</div>

@push('scripts')
<script>
function supervisorLocations() {
    return {
        realtime: false,
        fallbackTimer: null,

        init() {
            this.refreshStaleBadges();
            setInterval(() => this.refreshStaleBadges(), 15000);
            // Beri waktu Echo terinisialisasi (app.js initEcho async).
            setTimeout(() => this.connect(), 1500);
            window.addEventListener('echo:connected', () => this.connect());
            window.addEventListener('echo:disconnected', () => this.startFallback());
        },

        channels() {
            return [...document.querySelectorAll('[data-channel]')].map(el => el.dataset.channel).filter(Boolean);
        },

        connect() {
            if (!window.Echo) {
                this.startFallback();
                return;
            }
            this.realtime = true;
            if (this.fallbackTimer) {
                clearInterval(this.fallbackTimer);
                this.fallbackTimer = null;
            }
            const seen = new Set();
            this.channels().forEach((key) => {
                if (seen.has(key)) return;
                seen.add(key);
                try {
                    window.Echo.channel(`pkl.student.${key}`)
                        .listen('.pkl.location.updated', (e) => this.applyUpdate(key, e));
                } catch (err) {
                    console.warn('[pkl] gagal subscribe', key, err);
                }
            });
        },

        applyUpdate(key, e) {
            const row = document.querySelector(`[data-loc-row="${CSS.escape(key)}"]`);
            if (!row) return;
            const lat = row.querySelector('[data-col="lat"]');
            const lng = row.querySelector('[data-col="lng"]');
            const time = row.querySelector('[data-col="time"]');
            const badge = row.querySelector('[data-col="badge"]');
            if (lat) lat.textContent = e.latitude;
            if (lng) lng.textContent = e.longitude;
            if (time) time.textContent = 'baru saja';
            row.dataset.recordedAt = e.recorded_at || '';
            if (badge) badge.innerHTML = '<span class="md-badge md-badge-success">LIVE</span>';
            row.classList.add('bg-green-50');
            setTimeout(() => row.classList.remove('bg-green-50'), 2000);
        },

        refreshStaleBadges() {
            const now = Date.now();
            document.querySelectorAll('[data-loc-row]').forEach((row) => {
                const raw = row.dataset.recordedAt;
                if (!raw) return;
                const ts = new Date(raw.replace(' ', 'T')).getTime();
                if (Number.isNaN(ts)) return;
                const stale = (now - ts) > 120000;
                const badge = row.querySelector('[data-col="badge"]');
                if (badge) {
                    badge.innerHTML = stale
                        ? '<span class="md-badge md-badge-warning">STALE</span>'
                        : '<span class="md-badge md-badge-success">LIVE</span>';
                }
            });
        },

        // Fallback: polling ringan tiap 30 detik (bukan agresif) bila Echo tak tersedia.
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

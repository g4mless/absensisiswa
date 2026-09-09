@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-student-pkl')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Lokasi GPS</h1>
        <p class="text-gray-500">Pantau dan kirim lokasi Anda secara real-time</p>
    </div>

    <div x-data="gpsTracker()" x-init="init()">
        {{-- Status GPS + WebSocket --}}
        <x-card>
            <x-slot name="header">Status Koneksi</x-slot>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-3 w-3">
                        <span class="absolute inline-flex h-3 w-3 animate-ping rounded-full opacity-75"
                              :class="gpsFix ? 'bg-green-400' : 'bg-red-400'"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full"
                              :class="gpsFix ? 'bg-green-500' : 'bg-red-500'"></span>
                    </div>
                    <div>
                        <p class="text-base font-medium" :class="gpsFix ? 'text-green-700' : 'text-red-700'"
                           x-text="gpsFix ? 'GPS Aktif' : 'GPS Terputus'"></p>
                        <p class="text-sm text-gray-500" x-show="accuracy" x-text="'Akurasi ±' + Math.round(accuracy) + ' m'"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span x-show="wsStatus === 'connected'" class="md-badge md-badge-success">LIVE WEBSOCKET</span>
                    <span x-show="wsStatus === 'fallback'" class="md-badge md-badge-warning">MODE POLLING 20 dtk</span>
                    <span x-show="wsStatus === 'disconnected'" class="md-badge md-badge-danger">WS TERPUTUS</span>
                    <span x-show="isStale" class="md-badge md-badge-warning">STALE &gt; 2 mnt</span>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500" x-show="lastSentAt" x-text="'Terakhir terkirim: ' + lastSentAt"></p>
            <div class="mt-3 flex gap-2">
                <button type="button" @click="toggleTracking()" class="inline-flex min-h-[48px] items-center rounded-xl px-4 py-3 text-base font-medium"
                        :class="tracking ? 'text-red-600 hover:text-red-500' : 'text-green-600 hover:text-green-500'"
                        x-text="tracking ? 'Hentikan GPS' : 'Aktifkan GPS'">
                </button>
            </div>
        </x-card>

        {{-- Current Location --}}
        <x-card elevated>
            <x-slot name="header">Lokasi Saat Ini</x-slot>
            <div class="flex flex-col items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-100">
                    <svg class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0Z"/>
                    </svg>
                </div>

                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Latitude</p>
                    <p class="text-xl font-bold text-gray-900" x-text="latitude || '---'"></p>
                </div>

                <div class="text-center">
                    <p class="text-sm font-medium text-gray-500">Longitude</p>
                    <p class="text-xl font-bold text-gray-900" x-text="longitude || '---'"></p>
                </div>
            </div>
        </x-card>

        {{-- Send Location Button --}}
        <x-card>
            <div class="flex flex-col items-center gap-4">
                <div x-show="sendStatus" x-transition class="w-full">
                    <x-alert variant="info" x-text="sendStatus" dismissible />
                </div>

                <button
                    type="button"
                    @click="sendLocation()"
                    :disabled="sending || !latitude"
                    class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl bg-green-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-green-200 transition-all hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <template x-if="sending">
                        <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </template>
                    <template x-if="!sending">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                    </template>
                    <span x-text="sending ? 'Mengirim...' : 'Kirim Lokasi'"></span>
                </button>

                {{-- Auto-send toggle --}}
                <div class="flex min-h-[48px] items-center gap-3">
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" x-model="autoSend" class="peer sr-only">
                        <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-primary-600 peer-checked:after:translate-x-full"></div>
                    </label>
                    <span class="text-base text-gray-600">Kirim lokasi otomatis</span>
                </div>
                <p x-show="autoSend" class="text-sm text-gray-500">Lokasi dikirim otomatis setiap 20 detik (10–30 dtk sesuai PRD)</p>
            </div>
        </x-card>

        {{-- Location History --}}
        <x-card>
            <x-slot name="header">Riwayat Lokasi</x-slot>
            <x-slot name="subtitle">Lokasi yang dikirim hari ini</x-slot>

            <x-table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locationHistory ?? [] as $loc)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($loc->recorded_at ?? $loc->created_at)->format('H:i:s') }}</td>
                            <td>{{ $loc->latitude }}</td>
                            <td>{{ $loc->longitude }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-empty-state title="Belum ada riwayat" description="Belum ada lokasi yang dikirim hari ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    </div>
</div>

@push('scripts')
<script>
function gpsTracker() {
    return {
        latitude: null,
        longitude: null,
        accuracy: null,
        gpsFix: false,
        tracking: false,
        wsStatus: 'disconnected', // connected | fallback | disconnected
        isStale: false,
        lastSentAt: null,
        lastSentTs: null,
        sending: false,
        sendStatus: '',
        sendType: 'info',
        autoSend: false,
        watchId: null,
        autoSendInterval: null,
        staleTimer: null,

        init() {
            this.startTracking();
            this.detectEcho();

            // Auto-send 20 detik saat toggle aktif (PRD: 10–30 detik).
            this.$watch('autoSend', (value) => {
                if (value) {
                    this.sendLocation();
                    this.autoSendInterval = setInterval(() => {
                        if (this.latitude && this.longitude) this.sendLocation();
                    }, 20000);
                } else if (this.autoSendInterval) {
                    clearInterval(this.autoSendInterval);
                    this.autoSendInterval = null;
                }
            });

            // Tandai stale bila kiriman terakhir > 2 menit.
            this.staleTimer = setInterval(() => {
                this.isStale = this.lastSentTs ? (Date.now() - this.lastSentTs > 120000) : false;
            }, 10000);
        },

        detectEcho() {
            const update = () => {
                if (window.Echo) {
                    this.wsStatus = 'connected';
                } else {
                    // Echo belum siap (ENV/package belum dikonfigurasi) -> kirim HTTP tetap jalan (fallback).
                    this.wsStatus = 'fallback';
                }
            };
            update();
            window.addEventListener('echo:connected', () => { this.wsStatus = 'connected'; });
            window.addEventListener('echo:disconnected', () => { this.wsStatus = 'disconnected'; });
            setTimeout(update, 3000);
        },

        startTracking() {
            if (!navigator.geolocation) {
                this.sendStatus = 'Geolocation tidak didukung browser';
                this.sendType = 'danger';
                return;
            }

            this.tracking = true;
            this.watchId = navigator.geolocation.watchPosition(
                (position) => {
                    this.latitude = position.coords.latitude.toFixed(6);
                    this.longitude = position.coords.longitude.toFixed(6);
                    this.accuracy = position.coords.accuracy;
                    this.gpsFix = true;
                },
                (error) => {
                    this.gpsFix = false;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 5000 }
            );
        },

        toggleTracking() {
            if (this.tracking) {
                if (this.watchId !== null) {
                    navigator.geolocation.clearWatch(this.watchId);
                    this.watchId = null;
                }
                this.tracking = false;
                this.gpsFix = false;
            } else {
                this.startTracking();
            }
        },

        async sendLocation() {
            if (!this.latitude || !this.longitude || this.sending) return;

            this.sending = true;
            this.sendStatus = '';

            try {
                const response = await fetch(@json(route('student-pkl.location.send')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        latitude: this.latitude,
                        longitude: this.longitude,
                        accuracy: this.accuracy,
                    }),
                });

                const data = await response.json();

                if (response.ok) {
                    this.sendStatus = data.message || 'Lokasi berhasil dikirim!';
                    this.sendType = 'success';
                    this.lastSentTs = Date.now();
                    this.lastSentAt = data.recorded_at || new Date().toLocaleTimeString('id-ID');
                    this.isStale = false;
                } else {
                    this.sendStatus = data.message || 'Gagal mengirim lokasi';
                    this.sendType = 'danger';
                }
            } catch (e) {
                this.sendStatus = 'Terjadi kesalahan. Silakan coba lagi.';
                this.sendType = 'danger';
            } finally {
                this.sending = false;
            }
        }
    };
}
</script>
@endpush
@endsection

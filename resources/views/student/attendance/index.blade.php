@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-student')
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Absen Hari Ini</h1>
        <p class="text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div x-data="attendanceCheckin()" x-init="init()">
        {{-- GPS Status --}}
        <x-card>
            <x-slot name="header">Status GPS</x-slot>
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full"
                     :class="gpsReady ? 'bg-green-100' : (gpsError ? 'bg-red-100' : 'bg-yellow-100')">
                    <svg class="h-5 w-5" :class="gpsReady ? 'text-green-600' : (gpsError ? 'text-red-600' : 'text-yellow-600')" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium" :class="gpsReady ? 'text-green-700' : (gpsError ? 'text-red-700' : 'text-yellow-700')" x-text="gpsStatusText"></p>
                    <p class="text-xs text-gray-500" x-show="latitude && longitude" x-text="'Lat: ' + latitude + ', Lng: ' + longitude"></p>
                </div>
            </div>
        </x-card>

        {{-- Check-in Section --}}
        <x-card elevated>
            <div class="flex flex-col items-center gap-6 py-8">
                @if($todayAttendance ?? null)
                    {{-- Already checked in --}}
                    <div class="text-center">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">Sudah Check In</h3>
                        <p class="mt-1 text-sm text-gray-500">Anda sudah melakukan check-in hari ini</p>
                        <div class="mt-3">
                            <x-badge variant="success">{{ strtoupper($todayAttendance->status) }}</x-badge>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            Jam Masuk: {{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i') }}
                        </p>
                    </div>
                @elseif($attendanceAvailable)
                    {{-- Check-in form --}}
                    <div class="text-center">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-primary-100">
                            <svg class="h-10 w-10 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">Check In</h3>
                         <p class="mt-1 text-sm text-gray-500">Absensi dibuka pukul {{ substr($attendanceSetting->start_time, 0, 5) }} sampai {{ substr($attendanceSetting->end_time, 0, 5) }}</p>
                    </div>

                    @if($errors->any())
                        <div class="w-full max-w-md">
                            <x-alert variant="danger" title="Gagal Check In" dismissible>
                                <ul class="mt-1 list-disc list-inside text-sm">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </x-alert>
                        </div>
                    @endif

                    <div x-show="statusMessage" x-transition class="w-full max-w-md">
                        <div
                            x-data="{ show: true }"
                            x-show="show"
                            x-cloak
                            role="alert"
                            class="rounded-xl border p-4"
                            :class="{
                                'bg-green-50 border-green-200 text-green-800': statusType === 'success',
                                'bg-red-50 border-red-200 text-red-800': statusType === 'danger',
                                'bg-blue-50 border-blue-200 text-blue-800': statusType === 'info'
                            }"
                        >
                            <div class="flex items-start gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0" :class="{
                                    'text-green-400': statusType === 'success',
                                    'text-red-400': statusType === 'danger',
                                    'text-blue-400': statusType === 'info'
                                }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="flex-1 text-sm" x-text="statusMessage"></div>
                                <button type="button" x-on:click="show = false" class="shrink-0 hover:opacity-70 transition-opacity" :class="{
                                    'text-green-400': statusType === 'success',
                                    'text-red-400': statusType === 'danger',
                                    'text-blue-400': statusType === 'info'
                                }">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="checkIn()"
                        :disabled="loading || sampling || !gpsReady"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-primary-200 transition-all hover:bg-primary-700 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <template x-if="loading">
                            <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </template>
                        <template x-if="!loading">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                        <span x-text="loading ? (sampling ? samplingProgress || 'Mengumpulkan sampel GPS...' : 'Mengirim...') : 'Check In Sekarang'"></span>
                    </button>

                    <p x-show="sampling" class="text-xs text-blue-600" x-text="samplingProgress"></p>

                    <p class="text-xs text-gray-400">Pastikan GPS aktif dan Anda berada di area sekolah</p>
                @else
                    <div class="text-center py-8">
                        <h3 class="text-lg font-semibold text-gray-900">Absensi Ditutup</h3>
                        <p class="mt-2 text-sm text-gray-500">{{ $attendanceMessage }}</p>
                    </div>
                @endif
            </div>
        </x-card>
    </div>
</div>

@push('scripts')
<script>
function attendanceCheckin() {
    return {
        latitude: null,
        longitude: null,
        gpsReady: false,
        gpsError: false,
        gpsStatusText: 'Mendapatkan lokasi...',
        loading: false,
        sampling: false,
        samplingProgress: '',
        sampleCount: 0,
        targetSamples: 8,
        statusMessage: '',
        statusType: 'info',

        init() {
            this.getLocation();
        },

        getLocation() {
            if (!navigator.geolocation) {
                this.gpsError = true;
                this.gpsStatusText = 'Geolocation tidak didukung browser';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.latitude = position.coords.latitude.toFixed(6);
                    this.longitude = position.coords.longitude.toFixed(6);
                    this.gpsReady = true;
                    this.gpsStatusText = 'GPS Aktif - Siap check in';
                },
                (error) => {
                    this.gpsError = true;
                    this.gpsStatusText = 'GPS tidak aktif. Aktifkan lokasi di perangkat Anda.';
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        },

        collectSamples() {
            // Kumpulkan 5-10 sample dalam maksimal 10 detik via watchPosition.
            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    resolve([]);
                    return;
                }

                const samples = [];
                const maxSamples = 10;
                const maxDurationMs = 10000;
                this.sampling = true;
                this.sampleCount = 0;
                this.samplingProgress = 'Mengumpulkan sampel GPS (0/' + this.targetSamples + ')...';
                this.gpsStatusText = 'Mengumpulkan sampel GPS...';

                const watchId = navigator.geolocation.watchPosition(
                    (position) => {
                        const c = position.coords;
                        samples.push({
                            latitude: c.latitude,
                            longitude: c.longitude,
                            accuracy: c.accuracy ?? null,
                            timestamp: position.timestamp ?? Date.now(),
                            speed: c.speed ?? null,
                            heading: c.heading ?? null,
                            altitude: c.altitude ?? null,
                            altitude_accuracy: c.altitudeAccuracy ?? null,
                        });
                        this.sampleCount = samples.length;
                        this.samplingProgress = 'Mengumpulkan sampel GPS (' + samples.length + '/' + this.targetSamples + ')...';

                        // Update tampilan koordinat terakhir.
                        this.latitude = position.coords.latitude.toFixed(6);
                        this.longitude = position.coords.longitude.toFixed(6);

                        if (samples.length >= this.targetSamples) {
                            navigator.geolocation.clearWatch(watchId);
                            clearTimeout(timer);
                            this.sampling = false;
                            resolve(samples);
                        }
                    },
                    () => {
                        // Abaikan error sesaat, biarkan timeout yang memutuskan.
                    },
                    { enableHighAccuracy: true, maximumAge: 0, timeout: 10000 }
                );

                const timer = setTimeout(() => {
                    navigator.geolocation.clearWatch(watchId);
                    this.sampling = false;
                    resolve(samples);
                }, maxDurationMs);

                // Simpan agar tidak leak jika target tercapai duluan.
                if (samples.length >= maxSamples) {
                    navigator.geolocation.clearWatch(watchId);
                    clearTimeout(timer);
                    this.sampling = false;
                    resolve(samples);
                }
            });
        },

        async checkIn() {
            if (!this.gpsReady || this.loading || this.sampling) return;

            this.loading = true;
            this.statusMessage = '';

            try {
                const samples = await this.collectSamples();

                if (samples.length === 0) {
                    this.statusMessage = 'Gagal mendapatkan sampel GPS. Pastikan GPS aktif lalu coba lagi.';
                    this.statusType = 'danger';
                    this.loading = false;
                    this.getLocation();
                    return;
                }

                const last = samples[samples.length - 1];
                const payload = {
                    latitude: Number(last.latitude.toFixed(6)),
                    longitude: Number(last.longitude.toFixed(6)),
                    accuracy: last.accuracy,
                    samples: samples,
                };

                this.samplingProgress = 'Mengirim ' + samples.length + ' sampel ke server...';

                const response = await fetch('{{ route("student.attendance.checkin") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (response.ok) {
                    this.statusMessage = data.message || 'Check in berhasil!';
                    if (data.warning) {
                        this.statusMessage += ' ' + data.warning;
                    }
                    this.statusType = data.warning ? 'info' : 'success';
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    this.statusMessage = data.message || 'Gagal melakukan check in';
                    if (data.errors && data.errors.samples) {
                        this.statusMessage += ' (' + data.errors.samples.join(', ') + ')';
                    }
                    if (data.location_analysis && data.location_analysis.signals) {
                        const sigs = Object.keys(data.location_analysis.signals).join(', ');
                        const score = data.location_analysis.risk_score;
                        this.statusMessage += ' [Skor risiko ' + score + '/100, sinyal: ' + sigs + ']';
                    }
                    this.statusType = 'danger';
                    // Siapkan ulang GPS agar pengguna bisa retry setelah
                    // mematikan Fake GPS / mock location.
                    this.getLocation();
                }
            } catch (e) {
                this.statusMessage = 'Terjadi kesalahan. Silakan coba lagi.';
                this.statusType = 'danger';
            } finally {
                this.loading = false;
                this.sampling = false;
                this.samplingProgress = '';
            }
        }
    };
}
</script>
@endpush
@endsection

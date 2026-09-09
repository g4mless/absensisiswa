@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('teacher.attendance', ['date' => $session->date ?? request('date', date('Y-m-d'))]) }}" class="text-sm text-gray-500 hover:text-primary-600 transition-colors">Absensi</a>
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                <span class="text-sm font-medium text-gray-900">Sesi</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $session->subject->name ?? '-' }}</h1>
            <p class="text-gray-500">{{ $session->class->name ?? $session->classroom->name ?? '-' }} &middot; {{ $session->start_time }} - {{ $session->end_time }}</p>
            @php
                $attendanceStats = ['HADIR' => 0, 'IZIN' => 0, 'SAKIT' => 0, 'ALFA' => 0];
                foreach($session->students ?? [] as $s) {
                    $status = $s->pivot->status ?? 'ALFA';
                    $attendanceStats[$status] = ($attendanceStats[$status] ?? 0) + 1;
                }
            @endphp
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700">Hadir: <span id="summary-hadir">{{ $attendanceStats['HADIR'] }}</span></span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-yellow-50 px-3 py-1.5 text-xs font-semibold text-yellow-700">Izin: <span id="summary-izin">{{ $attendanceStats['IZIN'] }}</span></span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">Sakit: <span id="summary-sakit">{{ $attendanceStats['SAKIT'] }}</span></span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">Alfa: <span id="summary-alfa">{{ $attendanceStats['ALFA'] }}</span></span>
            </div>
        </div>
        <div class="flex gap-2">
            <span id="badge-done" class="{{ $session->attendance_completed ? '' : 'hidden' }}">
                <x-badge variant="success" class="text-sm px-3 py-1.5">
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    Absensi Selesai
                </x-badge>
            </span>
            <span id="badge-pending" class="{{ $session->attendance_completed ? 'hidden' : '' }}">
                <x-badge variant="warning" class="text-sm px-3 py-1.5">Belum Diisi</x-badge>
            </span>
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

    <div>
        <div>
            <x-card>
                <x-slot name="header">Daftar Kehadiran</x-slot>
                <x-slot name="subtitle">{{ count($session->students ?? []) }} siswa</x-slot>

                <div id="attendance-toast" class="hidden mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-2.5 text-sm font-medium text-green-700"></div>

                <form id="attendance-form" method="POST" action="{{ route('teacher.attendance.update', $session->id) }}" enctype="multipart/form-data" x-data="" autocomplete="off">
                    @csrf
                    <input type="hidden" name="date" value="{{ $session->date ?? request('date', date('Y-m-d')) }}" />

                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex gap-2">
                            <x-button type="button" variant="success" size="sm" onclick="markAll('HADIR')">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Semua Hadir
                            </x-button>
                            <x-button type="button" variant="danger" size="sm" onclick="markAll('ALFA')">
                                Semua Alfa
                            </x-button>
                        </div>
                        <x-button type="submit" variant="primary" size="sm">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Simpan Absensi
                        </x-button>
                    </div>

                    <div class="overflow-x-auto">
                        <x-table>
                            <thead>
                                <tr>
                                    <th class="w-12">No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th class="text-center min-w-[280px]">Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($session->students ?? [] as $index => $student)
                                    @php
                                        $currentStatus = $student->pivot->status ?? 'ALFA';
                                        $existingExcuse = $student->excuse_for_date ?? null;
                                    @endphp
                                    <tr>
                                        <td class="text-gray-500">{{ $index + 1 }}</td>
                                        <td class="font-mono text-sm">{{ $student->nis ?? '-' }}</td>
                                        <td class="font-medium">{{ $student->name ?? '-' }}</td>
                                        <td>
                                            <div class="flex items-center justify-center gap-2 sm:gap-3">
                                                @foreach(['HADIR' => 'H', 'IZIN' => 'I', 'SAKIT' => 'S', 'ALFA' => 'A'] as $status => $abbr)
                                                    <label class="inline-flex items-center gap-1.5 cursor-pointer group" title="{{ $status }}">
                                                        <input type="radio"
                                                            name="attendance[{{ $student->id }}]"
                                                            value="{{ $status }}"
                                                            {{ $currentStatus === $status ? 'checked' : '' }}
                                                            x-on:change="if (['IZIN', 'SAKIT'].includes($event.target.value)) $dispatch('open-modal', 'excuse-{{ $student->id }}')"
                                                            class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-500" />
                                                        <span class="text-xs font-semibold {{ match($status) {
                                                            'HADIR' => 'text-green-600 group-hover:text-green-700',
                                                            'IZIN' => 'text-yellow-600 group-hover:text-yellow-700',
                                                            'SAKIT' => 'text-blue-600 group-hover:text-blue-700',
                                                            'ALFA' => 'text-red-600 group-hover:text-red-700',
                                                            default => 'text-gray-600'
                                                        } }}">{{ $abbr }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <x-modal name="excuse-{{ $student->id }}" maxWidth="md">
                                                <x-slot name="header">Surat Izin/Sakit — {{ $student->name ?? '-' }}</x-slot>
                                                <div class="space-y-4">
                                                    <p class="text-xs text-gray-500">Status <span class="font-semibold" id="letter-status-{{ $student->id }}">Izin/Sakit</span> membutuhkan surat. Upload foto/scan surat (JPG, PNG, WebP, PDF, maks 5MB).</p>
                                                    @if($existingExcuse?->file_path)
                                                        <p class="text-xs text-gray-500">Surat saat ini: <a href="{{ asset('storage/' . $existingExcuse->file_path) }}" target="_blank" class="text-primary-600 hover:underline">Lihat</a> — upload baru untuk mengganti.</p>
                                                    @endif
                                                    <div>
                                                        <label class="mb-1 block text-xs font-medium text-gray-700">Gambar Surat</label>
                                                        <input type="file" name="excuse_file[{{ $student->id }}]" accept=".jpg,.jpeg,.png,.webp,.pdf" x-on:change="document.getElementById('letter-name-{{ $student->id }}').textContent = $event.target.files[0] ? 'Dipilih (ikut tersimpan saat Simpan Absensi): ' + $event.target.files[0].name : ''" class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-primary-700 hover:file:bg-primary-100" />
                                                        <p id="letter-name-{{ $student->id }}" class="mt-1 text-[11px] text-gray-500"></p>
                                                    </div>
                                                    <div>
                                                        <label class="mb-1 block text-xs font-medium text-gray-700">Keterangan (opsional)</label>
                                                        <textarea name="excuse_reason[{{ $student->id }}]" rows="2" placeholder="Contoh: Sakit demam, ada surat dokter..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">{{ old('excuse_reason.' . $student->id, $existingExcuse->reason ?? '') }}</textarea>
                                                    </div>
                                                </div>
                                                <x-slot name="footer">
                                                    <x-button variant="ghost" x-on:click="$dispatch('close-modal', 'excuse-{{ $student->id }}')">Nanti</x-button>
                                                    <x-button type="button" variant="primary" x-on:click="$dispatch('close-modal', 'excuse-{{ $student->id }}')">Simpan Surat</x-button>
                                                </x-slot>
                                            </x-modal>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <x-empty-state title="Tidak ada siswa" description="Tidak ada siswa yang terdaftar di sesi ini." />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </x-table>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.__attendanceDirty = window.__attendanceDirty || new Set();

    function markAll(status) {
        document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(radio => {
            radio.checked = true;
            window.__attendanceDirty.add(radio.name);
        });
    }

    // Polling realtime: refresh status kehadiran tiap 5 detik.
    // Baris yang sudah disentuh guru (dirty) tidak ditimpa.
    (function () {
        const form = document.getElementById('attendance-form');
        if (!form) return;

        const statusUrl = "{{ route('teacher.attendance.status', ['sessionId' => $session->id, 'date' => $session->date ?? request('date', date('Y-m-d'))]) }}";
        const names = @json(($session->students ?? collect())->mapWithKeys(fn ($s) => [(string) $s->id => ($s->name ?? '-')])->all());
        const last = {};

        form.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
            const match = radio.name.match(/^attendance\[(\d+)\]$/);
            if (match) last[match[1]] = radio.value;
        });

        form.addEventListener('change', function (event) {
            if (event.target.matches('input[type="radio"]')) {
                window.__attendanceDirty.add(event.target.name);
            }
        });

        let toastTimer = null;
        function toast(message) {
            const el = document.getElementById('attendance-toast');
            if (!el) return;
            el.textContent = message;
            el.classList.remove('hidden');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => el.classList.add('hidden'), 4000);
        }

        async function poll() {
            if (document.hidden) return;
            try {
                const response = await fetch(statusUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) return;
                const data = await response.json();

                ['hadir', 'izin', 'sakit', 'alfa'].forEach(key => {
                    const el = document.getElementById('summary-' + key);
                    if (el && data.summary && data.summary[key.toUpperCase()] !== undefined) {
                        el.textContent = data.summary[key.toUpperCase()];
                    }
                });

                const badgeDone = document.getElementById('badge-done');
                const badgePending = document.getElementById('badge-pending');
                if (badgeDone && badgePending && data.attendance_completed !== undefined) {
                    badgeDone.classList.toggle('hidden', !data.attendance_completed);
                    badgePending.classList.toggle('hidden', !!data.attendance_completed);
                }

                (data.students || []).forEach(student => {
                    const id = String(student.id);
                    const fieldName = 'attendance[' + id + ']';
                    if (window.__attendanceDirty.has(fieldName)) return; // jangan timpa edit guru
                    if (last[id] === student.status) return;
                    const radio = form.querySelector('input[name="' + fieldName + '"][value="' + student.status + '"]');
                    if (radio) {
                        radio.checked = true;
                        if (student.status === 'HADIR' && last[id] !== undefined && last[id] !== 'HADIR') {
                            toast((names[id] || 'Siswa') + ' baru saja absen');
                        }
                        last[id] = student.status;
                    }
                });
            } catch (error) {
                // Abaikan galat jaringan; coba lagi pada interval berikutnya.
            }
        }

        setInterval(poll, 5000);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) poll();
        });
    })();
</script>
@endpush
@endsection

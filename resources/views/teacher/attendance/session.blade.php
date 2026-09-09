@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-teacher')
@endsection

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <div class="min-w-0">
            <div class="mb-1 flex items-center gap-2">
                <a href="{{ route('teacher.attendance', ['date' => $session->date ?? request('date', date('Y-m-d'))]) }}" class="min-h-[44px] inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">Absensi</a>
                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                <span class="text-sm font-medium text-gray-900">Sesi</span>
            </div>
            <h1 class="truncate text-xl font-bold text-gray-900 sm:text-2xl">{{ $session->subject->name ?? '-' }}</h1>
            <p class="mt-0.5 truncate text-sm text-gray-500">{{ $session->class->name ?? $session->classroom->name ?? '-' }} &middot; {{ $session->start_time }} - {{ $session->end_time }}</p>
            @php
                $attendanceStats = ['HADIR' => 0, 'IZIN' => 0, 'SAKIT' => 0, 'ALFA' => 0];
                foreach($session->students ?? [] as $s) {
                    $status = $s->pivot->status ?? 'ALFA';
                    $attendanceStats[$status] = ($attendanceStats[$status] ?? 0) + 1;
                }
            @endphp
            <div class="mt-3 flex gap-2 overflow-x-auto no-scrollbar -mx-3 px-3 pb-1 sm:mx-0 sm:px-0 sm:flex-wrap">
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700">Hadir: <span id="summary-hadir">{{ $attendanceStats['HADIR'] }}</span></span>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-yellow-50 px-3 py-1.5 text-xs font-semibold text-yellow-700">Izin: <span id="summary-izin">{{ $attendanceStats['IZIN'] }}</span></span>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">Sakit: <span id="summary-sakit">{{ $attendanceStats['SAKIT'] }}</span></span>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">Alfa: <span id="summary-alfa">{{ $attendanceStats['ALFA'] }}</span></span>
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

                    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                        <div class="grid grid-cols-2 gap-2 sm:flex">
                            <x-button type="button" variant="success" size="sm" class="min-h-[44px] w-full sm:w-auto" onclick="markAll('HADIR')">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Semua Hadir
                            </x-button>
                            <x-button type="button" variant="danger" size="sm" class="min-h-[44px] w-full sm:w-auto" onclick="markAll('ALFA')">
                                Semua Alfa
                            </x-button>
                        </div>
                        <x-button type="submit" variant="primary" size="sm" class="min-h-[44px] w-full sm:w-auto">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Simpan Absensi
                        </x-button>
                    </div>

                    {{-- Mobile cards: big thumb-friendly segmented buttons --}}
                    <div class="space-y-2 md:hidden">
                        @forelse($session->students ?? [] as $index => $student)
                            @php $currentStatus = $student->pivot->status ?? 'ALFA'; @endphp
                            <div class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm" data-student-card="{{ $student->id }}">
                                <div class="mb-2.5 flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600">{{ $index + 1 }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-gray-900">{{ $student->name ?? '-' }}</p>
                                        <p class="font-mono text-xs text-gray-500">{{ $student->nis ?? '-' }}</p>
                                    </div>
                                    @if(($student->excuse_for_date ?? null)?->file_path)
                                        <span class="shrink-0 rounded-full bg-blue-50 px-2 py-1 text-[10px] font-semibold text-blue-700">Ada surat</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-4 gap-1.5" role="radiogroup" aria-label="Status {{ $student->name ?? '' }}">
                                    @foreach(['HADIR' => ['H', 'peer-checked:bg-green-600 peer-checked:border-green-600 peer-checked:text-white'], 'IZIN' => ['I', 'peer-checked:bg-yellow-500 peer-checked:border-yellow-500 peer-checked:text-white'], 'SAKIT' => ['S', 'peer-checked:bg-blue-600 peer-checked:border-blue-600 peer-checked:text-white'], 'ALFA' => ['A', 'peer-checked:bg-red-500 peer-checked:border-red-500 peer-checked:text-white']] as $status => [$abbr, $active])
                                        <label class="cursor-pointer">
                                            <input type="radio" name="attendance_m[{{ $student->id }}]" value="{{ $status }}" {{ $currentStatus === $status ? 'checked' : '' }}
                                                x-on:change="if (['IZIN', 'SAKIT'].includes($event.target.value)) $dispatch('open-modal', 'excuse-{{ $student->id }}')"
                                                class="peer sr-only" />
                                            <span class="flex min-h-[48px] flex-col items-center justify-center gap-0.5 rounded-xl border-2 border-gray-200 bg-white text-gray-500 transition-all {{ $active }}">{{ $abbr }}<span class="text-[9px] font-medium leading-none opacity-80">{{ ucfirst(strtolower($status)) }}</span></span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <x-empty-state title="Tidak ada siswa" description="Tidak ada siswa yang terdaftar di sesi ini." />
                        @endforelse
                    </div>

                    {{-- Desktop table --}}
                    <div class="hidden overflow-x-auto md:block">
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
                                    @php $currentStatus = $student->pivot->status ?? 'ALFA'; @endphp
                                    <tr>
                                        <td class="text-gray-500">{{ $index + 1 }}</td>
                                        <td class="font-mono text-sm">{{ $student->nis ?? '-' }}</td>
                                        <td class="font-medium">{{ $student->name ?? '-' }}</td>
                                        <td>
                                            <div class="flex items-center justify-center gap-1.5 sm:gap-2.5">
                                                @foreach(['HADIR' => 'H', 'IZIN' => 'I', 'SAKIT' => 'S', 'ALFA' => 'A'] as $status => $abbr)
                                                    @php
                                                        $accent = match($status) {
                                                            'HADIR' => 'accent-green-600 focus:ring-green-500',
                                                            'IZIN' => 'accent-yellow-500 focus:ring-yellow-500',
                                                            'SAKIT' => 'accent-blue-600 focus:ring-blue-500',
                                                            'ALFA' => 'accent-red-600 focus:ring-red-500',
                                                            default => 'accent-gray-500 focus:ring-gray-400',
                                                        };
                                                    @endphp
                                                    <label class="inline-flex items-center gap-1.5 cursor-pointer rounded-xl px-2.5 py-2 transition-colors hover:bg-gray-50 has-checked:bg-gray-100" title="{{ $status }}">
                                                        <input type="radio"
                                                            name="attendance[{{ $student->id }}]"
                                                            value="{{ $status }}"
                                                            {{ $currentStatus === $status ? 'checked' : '' }}
                                                            x-on:change="if (['IZIN', 'SAKIT'].includes($event.target.value)) $dispatch('open-modal', 'excuse-{{ $student->id }}')"
                                                            class="h-7 w-7 shrink-0 cursor-pointer border-2 border-gray-300 focus:ring-2 focus:ring-offset-1 {{ $accent }}" />
                                                        <span class="text-sm font-bold {{ match($status) {
                                                            'HADIR' => 'text-green-600',
                                                            'IZIN' => 'text-yellow-600',
                                                            'SAKIT' => 'text-blue-600',
                                                            'ALFA' => 'text-red-600',
                                                            default => 'text-gray-600'
                                                        } }}">{{ $abbr }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
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

                    {{-- Excuse modals (once per student, works for mobile + desktop) --}}
                    @foreach($session->students ?? [] as $student)
                        @php $existingExcuse = $student->excuse_for_date ?? null; @endphp
                        <x-modal name="excuse-{{ $student->id }}" maxWidth="md">
                            <x-slot name="header">Surat Izin/Sakit — {{ $student->name ?? '-' }}</x-slot>
                            <div class="space-y-4">
                                <p class="text-xs text-gray-500">Status <span class="font-semibold" id="letter-status-{{ $student->id }}">Izin/Sakit</span> membutuhkan surat. Upload foto/scan surat (JPG, PNG, WebP, PDF, maks 5MB).</p>
                                @if($existingExcuse?->file_path)
                                    <p class="text-xs text-gray-500">Surat saat ini: <a href="{{ asset('storage/' . $existingExcuse->file_path) }}" target="_blank" class="text-primary-600 hover:underline">Lihat</a> — upload baru untuk mengganti.</p>
                                @endif
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700">Gambar Surat</label>
                                    <input type="file" name="excuse_file[{{ $student->id }}]" accept=".jpg,.jpeg,.png,.webp,.pdf" x-on:change="document.getElementById('letter-name-{{ $student->id }}').textContent = $event.target.files[0] ? 'Dipilih (ikut tersimpan saat Simpan Absensi): ' + $event.target.files[0].name : ''" class="block w-full min-h-[44px] text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-primary-700 hover:file:bg-primary-100" />
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
                    @endforeach

                </form>
            </x-card>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.__attendanceDirty = window.__attendanceDirty || new Set();
    function _attId(name){ const m = name.match(/^attendance(?:_m)?\[(\d+)\]$/); return m ? m[1] : null; }

    function markAll(status) {
        document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(radio => {
            const id = _attId(radio.name);
            if (id) window.__attendanceDirty.add(id);
            radio.checked = true;
        });
        // sinkronkan kedua grup (mobile & desktop) agar peer-checked tampil
        document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(radio => { radio.checked = true; });
    }

    // Polling realtime: refresh status kehadiran tiap 5 detik.
    // Baris yang sudah disentuh guru (dirty) tidak ditimpa.
    (function () {
        const form = document.getElementById('attendance-form');
        if (!form) return;

        const statusUrl = "{{ route('teacher.attendance.status', ['sessionId' => $session->id, 'date' => $session->date ?? request('date', date('Y-m-d'))]) }}";
        const names = @json(($session->students ?? collect())->mapWithKeys(fn ($s) => [(string) $s->id => ($s->name ?? '-')])->all());
        const last = {};

        form.querySelectorAll('input[name^="attendance["]:checked, input[name^="attendance_m["]:checked').forEach(radio => {
            const id = _attId(radio.name);
            if (id && !last[id]) last[id] = radio.value;
        });

        form.addEventListener('change', function (event) {
            if (!event.target.matches('input[type="radio"]')) return;
            const id = _attId(event.target.name);
            if (!id) return;
            window.__attendanceDirty.add(id);
            const val = event.target.value;
            form.querySelectorAll('input[name="attendance[' + id + ']"][value="' + val + '"]').forEach(r => { r.checked = true; });
            form.querySelectorAll('input[name="attendance_m[' + id + ']"][value="' + val + '"]').forEach(r => { r.checked = true; });
        });

        // sebelum submit, salin nilai mobile ke field utama agar server menerima `attendance[]`
        form.addEventListener('submit', function () {
            form.querySelectorAll('input[name^="attendance_m["]:checked').forEach(radio => {
                const id = _attId(radio.name);
                if (!id) return;
                const target = form.querySelector('input[name="attendance[' + id + ']"][value="' + radio.value + '"]');
                if (target) target.checked = true;
            });
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
                    if (window.__attendanceDirty.has(id)) return; // jangan timpa edit guru
                    if (last[id] === student.status) return;
                    const real = form.querySelectorAll('input[name="attendance[' + id + ']"][value="' + student.status + '"]');
                    const mob = form.querySelectorAll('input[name="attendance_m[' + id + ']"][value="' + student.status + '"]');
                    const has = real.length || mob.length;
                    if (has) {
                        real.forEach(r => { r.checked = true; });
                        mob.forEach(r => { r.checked = true; });
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

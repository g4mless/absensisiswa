@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kalender Akademik</h1>
        <p class="text-gray-500">Perbarui kalender setiap bulan agar absensi hanya terbuka pada hari belajar.</p>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif
    @if($errors->any())
        <x-alert variant="danger" title="Gagal menyimpan" dismissible>
            {{ $errors->first() }}
        </x-alert>
    @endif

    <x-card>
        <x-slot name="header">Pengaturan Bulan</x-slot>
        <x-slot name="subtitle">Hari Sabtu dan Minggu otomatis tidak dapat digunakan untuk absensi.</x-slot>

        @if($calendar?->isLocked())
            <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 p-3 text-sm text-yellow-800">
                Kalender {{ $month->translatedFormat('F Y') }} sudah terkunci permanen sejak hari terakhir pukul 17.00.
            </div>
        @endif

        <form action="{{ route('admin.academic-calendar.index') }}" method="GET" class="mb-6 flex items-end gap-3">
            <x-input label="Pilih bulan" name="month" type="month" value="{{ $month->format('Y-m') }}" />
            <x-button variant="ghost" type="submit">Tampilkan</x-button>
        </form>

        <form action="{{ route('admin.academic-calendar.update') }}" method="POST" class="space-y-4" x-data="academicCalendar(@js(old('holiday_dates', $selectedHolidays)), @js(old('holiday_names', $holidayNames)))">
            @csrf
            @method('PUT')
            <input type="hidden" name="month" value="{{ $month->format('Y-m') }}" />
            <div>
                <p class="md-label mb-2">Klik tanggal yang menjadi hari libur</p>
                <div class="grid gap-2 text-center text-xs font-semibold text-gray-500" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                    @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $weekday)
                        <div>{{ $weekday }}</div>
                    @endforeach
                </div>
                <div class="mt-2 grid gap-2" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                    @foreach($calendarDays as $date)
                        <button type="button" @click="toggle('{{ $date->format('Y-m-d') }}')" :class="isSelected('{{ $date->format('Y-m-d') }}') ? 'border-red-400 bg-red-50 text-red-700' : '{{ $date->month === $month->month ? 'border-gray-200 bg-white text-gray-800' : 'border-gray-100 bg-gray-50 text-gray-300' }}'" class="rounded-lg border p-2 text-left transition-colors hover:border-red-300" style="min-height: 90px;">
                            <span class="block text-sm font-semibold">{{ $date->day }}</span>
                            <span x-show="isSelected('{{ $date->format('Y-m-d') }}')" class="mt-1 block text-[10px] font-medium">Libur</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <div x-show="selected.length" class="space-y-2">
                <p class="md-label">Keterangan hari libur</p>
                <template x-for="date in selected" :key="date">
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="holiday_dates[]" :value="date">
                        <span class="w-28 text-sm font-medium text-gray-700" x-text="formatDate(date)"></span>
                        <input type="text" class="md-input flex-1" :name="`holiday_names[${date}]`" x-model="names[date]" placeholder="Contoh: Libur nasional">
                    </div>
                </template>
            </div>
            <p class="text-xs text-gray-500">Klik ulang tanggal untuk membatalkan hari libur. Sabtu dan Minggu tetap otomatis tidak dapat digunakan untuk absensi.</p>
            <x-button variant="primary" type="submit" :disabled="$calendar?->isLocked()">Simpan Kalender Bulan Ini</x-button>
        </form>
    </x-card>
</div>
@push('scripts')
<script>
function academicCalendar(selected, names) {
    return {
        selected: selected || [],
        names: names || {},
        isSelected(date) { return this.selected.includes(date); },
        toggle(date) {
            if (this.isSelected(date)) {
                this.selected = this.selected.filter(item => item !== date);
                delete this.names[date];
            } else {
                this.selected.push(date);
                this.names[date] = '';
            }
        },
        formatDate(date) {
            return new Date(`${date}T00:00:00`).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        }
    };
}
</script>
@endpush
@endsection

@props([
    'students' => collect(),
    'selected' => null,
    'error' => null,
])

@php
    $selectedStudent = $students->firstWhere('id', (int) $selected);
    $studentOptions = $students->map(fn ($student) => [
        'id' => $student->id,
        'name' => $student->name,
        'nis' => $student->nis,
    ])->values();
@endphp

<div class="space-y-1.5" x-data="{
    query: @js($selectedStudent?->name ?? ''),
    selectedId: @js($selectedStudent?->id),
    open: false,
    students: @js($studentOptions),
    get filteredStudents() {
        const search = this.query.toLowerCase().trim();
        if (!search) return this.students;
        return this.students.filter(student =>
            student.name.toLowerCase().includes(search) ||
            student.nis.toLowerCase().includes(search)
        );
    },
    choose(student) {
        this.query = student.name;
        this.selectedId = student.id;
        this.open = false;
    }
}" @click.outside="open = false">
    <label for="student_search" class="md-label">Siswa</label>
    <input type="hidden" name="student_id" x-bind:value="selectedId">
    <div class="relative">
        <input
            id="student_search"
            type="text"
            x-model="query"
            @focus="open = true"
            @input="selectedId = null; open = true"
            @keydown.escape="open = false"
            autocomplete="off"
            placeholder="Ketik nama atau NIS siswa..."
            class="md-input{{ $error ? ' border-danger-500 focus:border-danger-500 focus:ring-danger-500/20' : '' }}"
            required
        >
        <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full max-h-56 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">
            <template x-for="student in filteredStudents" :key="student.id">
                <button type="button" @click="choose(student)" class="flex w-full flex-col px-3 py-2 text-left hover:bg-primary-50">
                    <span class="text-sm font-medium text-gray-800" x-text="student.name"></span>
                    <span class="text-xs text-gray-500" x-text="student.nis"></span>
                </button>
            </template>
            <p x-show="filteredStudents.length === 0" class="px-3 py-2 text-sm text-gray-500">Siswa tidak ditemukan.</p>
        </div>
    </div>
    @if($error)
        <p class="text-sm text-danger-500">{{ $error }}</p>
    @endif
</div>

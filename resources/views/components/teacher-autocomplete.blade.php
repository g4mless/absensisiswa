@props([
    'teachers' => collect(),
    'selected' => null,
    'error' => null,
])

@php
    $selectedTeacher = $teachers->firstWhere('id', (int) $selected);
    $teacherOptions = $teachers->map(fn ($teacher) => [
        'id' => $teacher->id,
        'name' => $teacher->name,
        'nip' => $teacher->nip,
    ])->values();
@endphp

<div class="space-y-1.5" x-data="{
    query: @js($selectedTeacher?->name ?? ''),
    selectedId: @js($selectedTeacher?->id),
    open: false,
    teachers: @js($teacherOptions),
    get filteredTeachers() {
        const search = this.query.toLowerCase().trim();
        if (!search) return this.teachers;
        return this.teachers.filter(teacher =>
            teacher.name.toLowerCase().includes(search) ||
            teacher.nip.toLowerCase().includes(search)
        );
    },
    choose(teacher) {
        this.query = teacher.name;
        this.selectedId = teacher.id;
        this.open = false;
    }
}" @click.outside="open = false">
    <label for="teacher_search" class="md-label">Guru</label>
    <input type="hidden" name="teacher_id" x-bind:value="selectedId">
    <div class="relative">
        <input
            id="teacher_search"
            type="text"
            x-model="query"
            @focus="open = true"
            @input="selectedId = null; open = true"
            @keydown.escape="open = false"
            autocomplete="off"
            placeholder="Ketik nama atau NIP guru..."
            class="md-input{{ $error ? ' border-danger-500 focus:border-danger-500 focus:ring-danger-500/20' : '' }}"
            required
        >
        <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full max-h-56 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">
            <template x-for="teacher in filteredTeachers" :key="teacher.id">
                <button type="button" @click="choose(teacher)" class="flex w-full flex-col px-3 py-2 text-left hover:bg-primary-50">
                    <span class="text-sm font-medium text-gray-800" x-text="teacher.name"></span>
                    <span class="text-xs text-gray-500" x-text="teacher.nip"></span>
                </button>
            </template>
            <p x-show="filteredTeachers.length === 0" class="px-3 py-2 text-sm text-gray-500">Guru tidak ditemukan.</p>
        </div>
    </div>
    @if($error)
        <p class="text-sm text-danger-500">{{ $error }}</p>
    @endif
</div>

@props([
    'teachers' => collect(),
    'selected' => null,
    'error' => null,
])

@php
    $selectedTeacher = $teachers->firstWhere('id', (int) $selected);
@endphp

<div class="space-y-1.5">
    <label for="teacher_name" class="md-label">Guru</label>
    <input
        id="teacher_name"
        type="text"
        name="teacher_name"
        value="{{ old('teacher_name', $selectedTeacher?->name ?? '') }}"
        autocomplete="off"
        placeholder="Ketik nama guru..."
        class="md-input{{ $error ? ' border-danger-500 focus:border-danger-500 focus:ring-danger-500/20' : '' }}"
        required
    >
    @if($error)
        <p class="text-sm text-danger-500">{{ $error }}</p>
    @endif
</div>

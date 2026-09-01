@props([
    'width' => '48',
])

@php
    $widthClass = match($width) {
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        '72' => 'w-72',
        'auto' => 'w-auto',
        default => 'w-48',
    };
@endphp

<div
    x-data="{ open: false }"
    @click.away="open = false"
    @keydown.escape="open = false"
    class="relative inline-block text-left"
>
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 {{ $widthClass }} origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
    >
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>

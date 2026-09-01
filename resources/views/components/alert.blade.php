@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $variantConfig = match($variant) {
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'text' => 'text-green-800',
            'iconColor' => 'text-green-400',
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'text' => 'text-yellow-800',
            'iconColor' => 'text-yellow-400',
            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
        ],
        'danger' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'text' => 'text-red-800',
            'iconColor' => 'text-red-400',
            'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        default => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'iconColor' => 'text-blue-400',
            'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    };
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-cloak
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="rounded-xl border {{ $variantConfig['bg'] }} {{ $variantConfig['border'] }} p-4"
    role="alert"
>
    <div class="flex items-start gap-3">
        <svg class="h-5 w-5 {{ $variantConfig['iconColor'] }} mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $variantConfig['icon'] }}" />
        </svg>

        <div class="flex-1">
            @if($title)
                <h4 class="text-sm font-semibold {{ $variantConfig['text'] }}">{{ $title }}</h4>
            @endif
            <div class="text-sm {{ $variantConfig['text'] }}">
                {{ $slot }}
            </div>
        </div>

        @if($dismissible)
            <button
                type="button"
                x-on:click="show = false"
                class="shrink-0 {{ $variantConfig['iconColor'] }} hover:opacity-70 transition-opacity"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>

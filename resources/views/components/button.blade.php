@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left',
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
])

@php
    $variantClasses = match($variant) {
        'primary' => 'md-btn-primary',
        'secondary' => 'md-btn-secondary',
        'danger' => 'md-btn-danger',
        'success' => 'md-btn-success',
        'ghost' => 'md-btn-ghost',
        default => 'md-btn-primary',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => '',
        'lg' => 'px-6 py-3 text-base',
        default => '',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "$variantClasses $sizeClasses", 'disabled' => $disabled || $loading]) }}
>
    @if($loading)
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    @elseif($icon && $iconPosition === 'left')
        <x-dynamic-component :component="$icon" class="w-4 h-4" />
    @endif

    {{ $slot }}

    @if($icon && $iconPosition === 'right' && !$loading)
        <x-dynamic-component :component="$icon" class="w-4 h-4" />
    @endif
</button>

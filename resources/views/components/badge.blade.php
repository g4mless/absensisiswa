@props([
    'variant' => 'neutral',
    'icon' => null,
])

@php
    $variantClasses = match($variant) {
        'success' => 'md-badge-success',
        'warning' => 'md-badge-warning',
        'danger' => 'md-badge-danger',
        'info' => 'md-badge-info',
        'neutral' => 'md-badge-neutral',
        default => 'md-badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => $variantClasses]) }}>
    @if($icon)
        <x-dynamic-component :component="$icon" class="w-3.5 h-3.5 -ml-0.5 mr-1" />
    @endif
    {{ $slot }}
</span>

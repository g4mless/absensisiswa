@props([
    'elevated' => false,
])

<div {{ $attributes->merge(['class' => $elevated ? 'md-elevated' : 'md-card']) }}>
    @if(isset($header))
        <div class="mb-4">
            @if(isset($subtitle))
                <h3 class="text-lg font-semibold text-gray-900">{{ $header }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
            @else
                <h3 class="text-lg font-semibold text-gray-900">{{ $header }}</h3>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>

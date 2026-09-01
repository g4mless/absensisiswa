@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
])

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="md-label">
            {{ $label }}
            @if($required)
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'md-input' . ($error ? ' border-danger-500 focus:border-danger-500 focus:ring-danger-500/20' : ''),
            'required' => $required,
            'disabled' => $disabled,
        ]) }}
    />

    @if($error)
        <p class="text-sm text-danger-500">{{ $error }}</p>
    @elseif($hint)
        <p class="text-sm text-gray-500">{{ $hint }}</p>
    @endif
</div>

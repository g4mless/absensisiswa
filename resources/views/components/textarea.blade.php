@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'rows' => 4,
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

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->merge([
            'class' => 'md-textarea' . ($error ? ' border-danger-500 focus:border-danger-500 focus:ring-danger-500/20' : ''),
            'required' => $required,
            'disabled' => $disabled,
        ]) }}
    >{{ $slot }}</textarea>

    @if($error)
        <p class="text-sm text-danger-500">{{ $error }}</p>
    @endif
</div>

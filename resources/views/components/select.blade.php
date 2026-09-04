@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'placeholder' => 'Select an option',
    'options' => [],
    'value' => null,
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

    <div class="relative">
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $attributes->merge([
                'class' => 'md-select md-select-with-icon' . ($error ? ' border-danger-500 focus:border-danger-500 focus:ring-danger-500/20' : ''),
                'required' => $required,
                'disabled' => $disabled,
            ]) }}
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif

            @foreach($options as $optionValue => $label)
                <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $value)>{{ $label }}</option>
            @endforeach

            {{ $slot }}
        </select>

        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    @if($error)
        <p class="text-sm text-danger-500">{{ $error }}</p>
    @endif
</div>

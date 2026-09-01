@props([
    'name' => 'file',
    'label' => null,
    'error' => null,
    'accept' => null,
    'multiple' => false,
    'maxSize' => null,
])

<div
    x-data="{ isDragging: false, fileName: '' }"
    class="space-y-1.5"
>
    @if($label)
        <label class="md-label">{{ $label }}</label>
    @endif

    <div
        x-on:dragover.prevent="isDragging = true"
        x-on:dragleave.prevent="isDragging = false"
        x-on:drop.prevent="isDragging = false; fileName = $event.dataTransfer.files[0].name"
        :class="isDragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 bg-gray-50'"
        class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-6 transition-colors cursor-pointer hover:border-primary-400 hover:bg-primary-50/50"
    >
        <input
            type="file"
            name="{{ $name }}"
            {{ $multiple ? 'multiple' : '' }}
            @if($accept) accept="{{ $accept }}" @endif
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            {{ $attributes }}
        />

        <svg class="w-10 h-10 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
        </svg>

        <div class="text-center">
            <p class="text-sm text-gray-600">
                <span class="font-semibold text-primary-600">Click to upload</span>
                or drag and drop
            </p>
            @if($maxSize)
                <p class="text-xs text-gray-500 mt-1">{{ $maxSize }}</p>
            @endif
        </div>

        <p x-show="fileName" x-text="fileName" class="mt-2 text-sm text-primary-600 font-medium"></p>
    </div>

    @if($error)
        <p class="text-sm text-danger-500">{{ $error }}</p>
    @endif
</div>

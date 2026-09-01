@props([
    'title' => 'No results found',
    'description' => null,
    'icon' => null,
])

<div class="text-center py-12 px-4">
    @if($icon)
        <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
            <x-dynamic-component :component="$icon" class="w-8 h-8 text-gray-400" />
        </div>
    @endif

    <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>

    @if($description)
        <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
    @endif

    @if(isset($actions))
        <div class="mt-4">
            {{ $actions }}
        </div>
    @endif
</div>

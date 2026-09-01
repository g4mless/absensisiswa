@props([
    'striped' => false,
    'hoverable' => true,
])

<div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'md-table']) }}>
            {{ $slot }}
        </table>
    </div>
</div>

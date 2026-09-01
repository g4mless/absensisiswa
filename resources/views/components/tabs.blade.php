@props([
    'tabs' => [],
    'active' => null,
])

<div x-data="{ activeTab: '{{ $active ?? ($tabs[0]['name'] ?? '') }}' }">
    <div class="border-b border-gray-200">
        <nav class="flex gap-6 -mb-px" role="tablist">
            @foreach($tabs as $tab)
                <button
                    type="button"
                    role="tab"
                    x-on:click="activeTab = '{{ $tab['name'] }}'"
                    :class="activeTab === '{{ $tab['name'] }}' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium transition-colors"
                    :aria-selected="activeTab === '{{ $tab['name'] }}'"
                >
                    @if(isset($tab['icon']))
                        <x-dynamic-component :component="$tab['icon']" class="w-4 h-4 inline-block mr-1.5 -mt-0.5" />
                    @endif
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    <div class="mt-4">
        @foreach($tabs as $tab)
            <div
                x-show="activeTab === '{{ $tab['name'] }}'"
                x-cloak
                role="tabpanel"
            >
                @if(isset(${$tab['name']}))
                    {{ ${$tab['name']} }}
                @endif
            </div>
        @endforeach

        {{ $slot }}
    </div>
</div>

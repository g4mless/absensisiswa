{{-- Topbar --}}
<header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-gray-200 bg-white/80 px-4 backdrop-blur-sm sm:px-6" x-data="{ dropdownOpen: false }">
    @if(!in_array(auth()->user()->role ?? '', ['siswa', 'siswa_pkl']))
        {{-- Mobile hamburger --}}
        <button
            @click="sidebarOpen = !sidebarOpen"
            class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100 lg:hidden"
        >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
            </svg>
        </button>
    @endif

    {{-- Page title --}}
    <div class="flex-1">
        <h2 class="text-lg font-semibold text-gray-900">{{ $pageTitle ?? 'Dashboard' }}</h2>
        @isset($pageSubtitle)
            <p class="text-xs text-gray-500">{{ $pageSubtitle }}</p>
        @endisset
    </div>

    {{-- Notification bell --}}
    <button class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
        </svg>
        <span class="absolute right-1.5 top-1.5 flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
        </span>
    </button>

    {{-- User dropdown --}}
    <div class="relative">
        <button
            @click="dropdownOpen = !dropdownOpen"
            @keydown.escape.window="dropdownOpen = false"
            class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-gray-100"
        >
            <span class="hidden text-sm font-medium text-gray-700 sm:block">{{ Auth::user()->name ?? 'User' }}</span>
            <svg class="hidden h-4 w-4 text-gray-400 sm:block" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>

        <div
            x-show="dropdownOpen"
            @click.away="dropdownOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl bg-white py-1 shadow-lg ring-1 ring-black/5"
            style="display: none;"
        >
            <div class="border-b border-gray-100 px-4 py-3">
                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name ?? 'User' }}</p>
            </div>
            @php
                $profileRoute = match(auth()->user()->role ?? '') {
                    'admin' => route('admin.profile'),
                    'guru' => route('teacher.profile'),
                    'siswa' => route('student.profile'),
                    'siswa_pkl' => route('student-pkl.profile'),
                    default => '#',
                };
            @endphp
            <a href="{{ $profileRoute }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
                Profil
            </a>
            <div class="md-divider mx-3 my-1"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
</header>

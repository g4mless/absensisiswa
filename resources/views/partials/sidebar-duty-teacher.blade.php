{{-- Guru Piket Sidebar --}}
<aside
    class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-gray-200 bg-white transition-transform duration-300 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    {{-- Logo --}}
    <div class="flex h-16 items-center gap-3 border-b border-gray-200 px-6">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-transparent">
            <img src="{{ asset('logo.png') }}" alt="Logo sekolah" class="h-10 w-10 object-contain">
            <svg class="hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342"/>
            </svg>
        </div>
        <div>
            <span class="text-sm font-bold text-gray-900">Absensi Sekolah</span>
            <p class="text-[10px] text-gray-400">Panel Guru Piket</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <div class="space-y-1">
            {{-- Dashboard --}}
            <a href="{{ route('duty-teacher.dashboard') }}" class="{{ request()->routeIs('duty-teacher.dashboard') ? 'md-sidebar-link-active' : 'md-sidebar-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                <span>Dashboard</span>
            </a>

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Absensi</p>
            </div>

            {{-- Today's Attendance --}}
            <a href="{{ route('duty-teacher.attendance.today') }}" class="{{ request()->routeIs('duty-teacher.attendance.today') ? 'md-sidebar-link-active' : 'md-sidebar-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                <span>Absensi Hari Ini</span>
            </a>

            {{-- All Attendance --}}
            <a href="{{ route('duty-teacher.attendance.all') }}" class="{{ request()->routeIs('duty-teacher.attendance.all') ? 'md-sidebar-link-active' : 'md-sidebar-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>Semua Absensi</span>
            </a>

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Laporan</p>
            </div>

            {{-- Semester Reports --}}
            <a href="{{ route('duty-teacher.reports.semester') }}" class="{{ request()->routeIs('duty-teacher.reports.semester') ? 'md-sidebar-link-active' : 'md-sidebar-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                <span>Rekap Semester</span>
            </a>
        </div>
    </nav>

    {{-- User info --}}
    <div class="border-t border-gray-200 p-4">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700">
                {{ substr(Auth::user()->name ?? 'GP', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name ?? 'Guru Piket' }}</p>
                <p class="text-xs text-gray-500 truncate">Guru Piket</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-red-500" title="Keluar">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Absensi Sekolah') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        {{-- Branding --}}
        <div class="mb-8 text-center">
            <img src="{{ asset('logo.png') }}" alt="Logo sekolah" class="mx-auto h-20 w-20 object-contain">
            {{--
                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                </svg>
            --}}
            <h1 class="mt-4 text-2xl font-bold text-gray-900">{{ config('app.name', 'Absensi Sekolah') }}</h1>
            <p class="mt-1 text-sm text-gray-500">Sistem Manajemen Absensi Sekolah</p>
        </div>

        {{-- Content card --}}
        <div class="w-full max-w-md">
            <div class="md-elevated">
                @yield('content')
            </div>
        </div>

        {{-- Footer --}}
        <p class="mt-8 text-center text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'Absensi Sekolah') }}. All rights reserved.</p>
    </div>

    @stack('scripts')
</body>
</html>

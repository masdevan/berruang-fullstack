<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0A0A0A">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">

    @vite(['resources/css/app.css', 'resources/js/auth.js'])

    @stack('scripts')
</head>
<body class="font-sans antialiased bg-[#0A0A0A] text-white touch-manipulation">
    <div class="min-h-dvh flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                @if ($__env->hasSection('brand'))
                    @yield('brand')
                @else
                    <img src="{{ asset('logo.png') }}" alt="BerRuang" class="h-20 mx-auto">
                @endif
            </div>

            <x-auth.alert />

            @yield('content')
        </div>
    </div>
</body>
</html>

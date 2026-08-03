<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    @foreach (['400', '500', '600', '700'] as $weight)
        <link rel="preload" as="font" type="font/woff2" crossorigin
              href="{{ Vite::asset('node_modules/@fontsource/inter/files/inter-latin-'.$weight.'-normal.woff2') }}">
    @endforeach

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">

    @vite(['resources/css/app.css', 'resources/js/chat.js'])
</head>
<body class="font-sans antialiased bg-[#0A0A0A] text-white h-screen overflow-hidden js-loading" data-user-id="{{ auth()->id() ?? '' }}">
    <div id="top-loader" class="fixed top-0 left-0 right-0 h-0.5 z-100 pointer-events-none overflow-hidden">
        <div id="top-loader-bar" class="h-full bg-[#E091A9] rounded-r-full transition-[width] duration-700 ease-out" style="width: 0%"></div>
    </div>
    <div class="flex h-screen">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
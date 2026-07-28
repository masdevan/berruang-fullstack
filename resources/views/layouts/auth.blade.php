<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/auth.js'])
</head>
<body class="font-sans antialiased bg-[#0A0A0A] text-white">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <h1 class="text-xl font-semibold tracking-tight text-white">BerRuang</h1>
            </div>

            <x-auth.alert />

            @yield('content')
        </div>
    </div>
</body>
</html>

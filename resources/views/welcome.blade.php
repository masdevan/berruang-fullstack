<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="font-sans antialiased bg-[#0A0A0A]">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/4 flex items-center justify-center mx-auto mb-6">
                <x-icons.chat-bubble class="w-8 h-8 text-[#E091A9]" />
            </div>
            <h1 class="text-2xl font-semibold tracking-tight text-white">Welcome to BerRuang</h1>
            <p class="text-white/35 mt-1.5">You're signed in</p>
        </div>
        <div class="mt-8">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="px-5 py-2.5 bg-[#E091A9] text-[#0A0A0A] text-sm font-medium rounded-xl hover:bg-[#E8A8BC] active:scale-[0.98] transition-all duration-150 cursor-pointer">
                    Sign out
                </button>
            </form>
        </div>
    </div>
</body>
</html>

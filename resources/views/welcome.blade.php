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
                <svg class="w-8 h-8 text-[#E091A9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                </svg>
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

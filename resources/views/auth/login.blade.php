@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <x-auth.input name="email" type="email" placeholder="Email" required autofocus />

        <div class="relative">
            <x-auth.input name="password" type="password" placeholder="Password" required class="pr-10" />
            <button type="button" onclick="togglePassword('password')"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-white/25 hover:text-white/50 transition-colors cursor-pointer">
                <svg id="eye-password" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <svg id="eye-off-password" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                </svg>
            </button>
        </div>

        <div class="flex items-center justify-between flex-wrap gap-y-1">
            <label class="flex items-center gap-2 cursor-pointer group">
                <input type="checkbox" name="remember"
                       class="w-4 h-4 border-white/10 bg-white/3 text-[#E091A9] focus:ring-[#E091A9]/30 focus:ring-offset-0 cursor-pointer">
                <span class="text-sm text-white/40 group-hover:text-white/60 transition-colors">Remember me</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-white/35 hover:text-[#E091A9] transition-colors">Forgot password?</a>
        </div>

        <x-auth.button>Sign in</x-auth.button>
    </form>

    <div class="mt-4">
        <a href="{{ route('auth.google') }}"
           class="w-full flex items-center justify-center gap-2.5 py-2.5 px-4 bg-white/3 border border-white/6 text-sm text-white/70 font-medium hover:bg-white/6 hover:text-white active:scale-[0.98] transition-all duration-150 cursor-pointer rounded-lg">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Google
        </a>
    </div>

    <p class="text-sm text-white/25 text-center mt-5">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-white/50 hover:text-[#E091A9] transition-colors">Create one</a>
    </p>
@endsection

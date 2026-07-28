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
                <x-icons.eye id="eye-password" />
                <x-icons.eye-off id="eye-off-password" class="w-4 h-4 hidden" />
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
            <x-icons.google />
            Google
        </a>
    </div>

    <p class="text-sm text-white/25 text-center mt-5">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-white/50 hover:text-[#E091A9] transition-colors">Create one</a>
    </p>
@endsection

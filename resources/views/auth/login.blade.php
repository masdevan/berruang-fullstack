@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <x-auth.input name="email" type="email" placeholder="Email" required autofocus />

        <x-auth.input name="password" type="password" placeholder="Password" required />

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

    <p class="text-sm text-white/25 text-center mt-5">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-white/50 hover:text-[#E091A9] transition-colors">Create one</a>
    </p>
@endsection

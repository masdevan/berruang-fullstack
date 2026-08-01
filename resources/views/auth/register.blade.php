@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <x-auth.input name="first_name" placeholder="First name" required autofocus oninput="generateUsername()" />
        <x-auth.input name="last_name" placeholder="Last name" oninput="generateUsername()" />

        <div class="relative">
            <x-auth.input name="username" placeholder="Username" required class="pr-10" />
            <x-icons.check id="username-check" class="hidden w-4 h-4 text-green-400/80 absolute right-3 top-1/2 -translate-y-1/2" />
            <x-icons.x id="username-error" class="hidden w-4 h-4 text-red-400/80 absolute right-3 top-1/2 -translate-y-1/2" />
            <x-icons.spinner id="username-spinner" class="hidden w-4 h-4 text-white/30 animate-spin absolute right-3 top-1/2 -translate-y-1/2" />
        </div>

        <x-auth.input name="email" type="email" placeholder="Email" required />

        <div>
            <x-auth.input name="password" type="password" placeholder="Password" required oninput="checkPasswordStrength(this.value)" />
            <div id="password-strength" class="mt-3 hidden">
                <div class="flex gap-1 mb-1.5">
                    <div id="str-0" class="h-0.5 flex-1 bg-white/6 transition-all duration-300"></div>
                    <div id="str-1" class="h-0.5 flex-1 bg-white/6 transition-all duration-300"></div>
                    <div id="str-2" class="h-0.5 flex-1 bg-white/6 transition-all duration-300"></div>
                </div>
                <p id="strength-label" class="text-xs text-white/30"></p>
            </div>
        </div>

        <x-auth.input name="password_confirmation" type="password" placeholder="Confirm password" required />

        <x-auth.button>Create account</x-auth.button>
    </form>

    <p class="text-sm text-white/25 text-center mt-5">
        Already have an account?
        <a href="{{ route('login') }}" class="text-white/50 hover:text-[#E091A9] transition-colors">Sign in</a>
    </p>
@endsection

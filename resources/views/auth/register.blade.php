@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <x-auth.input name="name" placeholder="Name" required autofocus oninput="generateUsername(this.value)" />

        <x-auth.input name="username" placeholder="Username" required />
        <p id="username-status" class="text-xs mt-1 hidden"></p>

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


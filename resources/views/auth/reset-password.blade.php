@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    @if (! session('reset_code_verified'))
        <div class="text-center mb-6">
            <p class="text-sm text-white/35">Enter the 6-digit code sent to</p>
            <p class="text-sm font-medium text-white/70">{{ session('reset_email') }}</p>
        </div>

        <form method="POST" action="{{ route('password.verify-code') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="email" value="{{ session('reset_email') }}">

            <div>
                <x-auth.code-input />
                @error('code')
                    <p class="text-xs text-red-400/80 text-center mt-3">{{ $message }}</p>
                @enderror
            </div>

            <x-auth.button>Verify code</x-auth.button>
        </form>

        <form method="POST" action="{{ route('password.email') }}" class="text-center mt-5">
            @csrf
            <input type="hidden" name="email" value="{{ session('reset_email') }}">
            <span class="text-sm text-white/25">Didn't receive it?</span>
            <button type="submit" class="text-sm text-white/50 hover:text-[#E091A9] transition-colors cursor-pointer">Resend code</button>
        </form>
    @else
        <p class="text-sm text-white/35 text-center mb-6">Choose a new password for {{ session('reset_email') }}</p>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <div>
                <div class="relative">
                    <x-auth.input name="password" type="password" placeholder="New password" required class="pr-10"
                                  oninput="checkPasswordStrength(this.value)" />
                    <button type="button" onclick="togglePassword('password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-white/25 hover:text-white/50 transition-colors cursor-pointer">
                        <x-icons.eye id="eye-password" />
                        <x-icons.eye-off id="eye-off-password" class="w-4 h-4 hidden" />
                    </button>
                </div>
                <div id="password-strength" class="mt-3 hidden">
                    <div class="flex gap-1 mb-1.5">
                        <div id="str-0" class="h-0.5 flex-1 bg-white/6 transition-all duration-300"></div>
                        <div id="str-1" class="h-0.5 flex-1 bg-white/6 transition-all duration-300"></div>
                        <div id="str-2" class="h-0.5 flex-1 bg-white/6 transition-all duration-300"></div>
                    </div>
                    <p id="strength-label" class="text-xs text-white/30"></p>
                </div>
            </div>

            <div class="relative">
                <x-auth.input name="password_confirmation" type="password" placeholder="Confirm new password" required class="pr-10" />
                <button type="button" onclick="togglePassword('password_confirmation')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-white/25 hover:text-white/50 transition-colors cursor-pointer">
                    <x-icons.eye id="eye-password_confirmation" />
                    <x-icons.eye-off id="eye-off-password_confirmation" class="w-4 h-4 hidden" />
                </button>
            </div>

            <x-auth.button>Reset password</x-auth.button>
        </form>
    @endif

    <p class="text-sm text-white/25 text-center mt-5">
        <a href="{{ route('login') }}" class="text-white/50 hover:text-[#E091A9] transition-colors">Back to sign in</a>
    </p>
@endsection

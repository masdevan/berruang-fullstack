@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    <div class="text-center mb-6">
        <p class="text-sm text-white/35">We sent a 6-digit code to</p>
        <p class="text-sm font-medium text-white/70">{{ auth()->user()->email }}</p>
    </div>

    <form method="POST" action="{{ route('verification.verify') }}" class="space-y-6">
        @csrf

        <div>
            <x-auth.code-input />
            @error('code')
                <p class="text-xs text-red-400/80 text-center mt-3">{{ $message }}</p>
            @enderror
        </div>

        <x-auth.button>Verify email</x-auth.button>
    </form>

    <form method="POST" action="{{ route('verification.resend') }}" class="text-center mt-5">
        @csrf
        <span class="text-sm text-white/25">Didn't receive it?</span>
        <button type="submit" class="text-sm text-white/50 hover:text-[#E091A9] transition-colors cursor-pointer">Resend code</button>
    </form>

@endsection

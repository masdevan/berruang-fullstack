@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <x-auth.input name="email" type="email" placeholder="Email" required autofocus />

        <x-auth.button>Send reset code</x-auth.button>
    </form>

    <p class="text-sm text-white/25 text-center mt-5">
        <a href="{{ route('login') }}" class="text-white/50 hover:text-[#E091A9] transition-colors">Back to sign in</a>
    </p>
@endsection

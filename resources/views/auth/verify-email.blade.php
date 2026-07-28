@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    <form method="POST" action="{{ route('verification.verify') }}" class="space-y-6">
        @csrf

        <div>
            <p class="text-sm text-white/35 text-center mb-4">Enter the 6-digit code sent to your email</p>
            <div class="flex gap-2 sm:gap-2.5 justify-center" id="code-inputs">
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" name="code[]" id="code-{{ $i }}" maxlength="1" inputmode="numeric"
                           autocomplete="one-time-code"
                            class="w-10 sm:w-12 h-[3.25rem] text-center text-sm sm:text-base bg-white/3 border border-white/6 text-white focus:outline-none focus:border-[#E091A9]/50 focus:bg-white/5 transition-all duration-200"
                           oninput="handleCodeInput(this, {{ $i }})"
                           onkeydown="handleCodeKeydown(event, {{ $i }})">
                @endfor
            </div>
        </div>

        <x-auth.button>Verify email</x-auth.button>
    </form>

    <p class="text-sm text-white/25 text-center mt-5">
        Didn't receive it?
        <a href="{{ route('verification.resend') }}" class="text-white/50 hover:text-[#E091A9] transition-colors">Resend code</a>
    </p>

    <script>
        function handleCodeInput(input, index) {
            input.value = input.value.replace(/\D/g, '');
            if (input.value && index < 5) {
                document.getElementById('code-' + (index + 1)).focus();
            }
        }

        function handleCodeKeydown(event, index) {
            if (event.key === 'Backspace' && !event.target.value && index > 0) {
                document.getElementById('code-' + (index - 1)).focus();
            }
        }
    </script>
@endsection

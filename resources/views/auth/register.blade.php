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
            <div class="relative">
                <x-auth.input name="password" type="password" placeholder="Password" required class="pr-10" oninput="checkPasswordStrength(this.value)" />
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
            <x-auth.input name="password_confirmation" type="password" placeholder="Confirm password" required class="pr-10" />
            <button type="button" onclick="togglePassword('password_confirmation')"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-white/25 hover:text-white/50 transition-colors cursor-pointer">
                    <x-icons.eye id="eye-password_confirmation" />
                    <x-icons.eye-off id="eye-off-password_confirmation" class="w-4 h-4 hidden" />
            </button>
        </div>

        <x-auth.button>Create account</x-auth.button>
    </form>

    <p class="text-sm text-white/25 text-center mt-5">
        Already have an account?
        <a href="{{ route('login') }}" class="text-white/50 hover:text-[#E091A9] transition-colors">Sign in</a>
    </p>
@endsection

<script>
    let autoGen = true;

    function generateUsername(name) {
        if (!autoGen) return;
        const slug = name.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
        document.querySelector('[name="username"]').value = slug;
        checkUsername(slug);
    }

    document.querySelector('[name="username"]').addEventListener('input', function () {
        if (this.value === '') return;
        autoGen = false;
        checkUsername(this.value);
    });

    document.querySelector('[name="name"]').addEventListener('input', function () {
        autoGen = true;
    });

    let timeout;
    function checkUsername(val) {
        clearTimeout(timeout);
        const status = document.getElementById('username-status');
        if (!val) { status.classList.add('hidden'); return; }
        timeout = setTimeout(() => {
            fetch('/check-username/' + encodeURIComponent(val))
                .then(r => r.json())
                .then(d => {
                    status.classList.remove('hidden');
                    if (d.taken) {
                        status.className = 'text-xs mt-1 text-red-400/80';
                        status.textContent = 'Username already taken';
                    } else {
                        status.className = 'text-xs mt-1 text-green-400/60';
                        status.textContent = 'Username available';
                    }
                });
        }, 300);
    }
</script>

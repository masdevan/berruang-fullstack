<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function attemptLogin(array $credentials, bool $remember): false|string|null
    {
        if (! Auth::attempt($credentials, $remember)) {
            return false;
        }

        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            return 'verification.notice';
        }

        return $user->onboarded_at ? null : 'setup-profile';
    }

    public function register(array $data): User
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->sendEmailVerificationNotification();
        Auth::login($user);

        return $user;
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function verifyEmailCode(User $user, array $codeDigits): string
    {
        $result = app(EmailCodeService::class)->attemptVerify(
            $user->email,
            EmailCodeService::PURPOSE_VERIFY_EMAIL,
            $codeDigits,
        );

        if ($result !== EmailCodeService::STATUS_SUCCESS) {
            return $result;
        }

        $user->markEmailAsVerified();

        return $user->onboarded_at ? 'messages' : 'setup-profile';
    }

    public function resendVerification(User $user): bool
    {
        return $user->sendEmailVerificationNotification();
    }
}

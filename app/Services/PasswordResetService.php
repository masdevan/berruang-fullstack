<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function sendResetCode(string $email): array
    {
        $user = User::where('email', $email)->first();

        return [
            'exists' => $user !== null,
            'sent' => $user ? app(EmailCodeService::class)->sendPasswordResetCode($user->email) : false,
        ];
    }

    public function verifyCode(string $email, array $codeDigits): ?string
    {
        $result = app(EmailCodeService::class)->attemptVerify(
            $email,
            EmailCodeService::PURPOSE_RESET_PASSWORD,
            $codeDigits,
        );

        return match ($result) {
            EmailCodeService::STATUS_SUCCESS => null,
            EmailCodeService::STATUS_THROTTLED => 'Too many attempts. Please wait a minute and try again.',
            default => 'Invalid or expired code. Please try again.',
        };
    }

    public function resetPassword(string $email, string $password): void
    {
        $user = User::where('email', $email)->firstOrFail();

        $user->forceFill([
            'password' => Hash::make($password),
        ])->save();

        $user->setRememberToken(Str::random(60));
        $user->save();
    }
}

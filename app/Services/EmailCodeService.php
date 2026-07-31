<?php

namespace App\Services;

use App\Mail\PasswordResetCodeMail;
use App\Mail\VerificationCodeMail;
use App\Models\EmailCode;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class EmailCodeService
{
    public const PURPOSE_VERIFY_EMAIL = 'verify_email';

    public const PURPOSE_RESET_PASSWORD = 'reset_password';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_INVALID = 'invalid';

    public const STATUS_THROTTLED = 'throttled';

    private const CODE_EXPIRY_MINUTES = 10;

    private const RESEND_THROTTLE_SECONDS = 60;

    private const MAX_VERIFY_ATTEMPTS = 5;

    public function sendVerificationCode(string $email): bool
    {
        return $this->send($email, self::PURPOSE_VERIFY_EMAIL);
    }

    public function sendPasswordResetCode(string $email): bool
    {
        return $this->send($email, self::PURPOSE_RESET_PASSWORD);
    }

    public function attemptVerify(string $email, string $purpose, array $codeDigits): string
    {
        $attemptKey = "email_code_attempts:{$purpose}:{$email}";

        if (! RateLimiter::attempt($attemptKey, self::MAX_VERIFY_ATTEMPTS, fn () => true)) {
            return self::STATUS_THROTTLED;
        }

        $code = implode('', $codeDigits);

        $record = EmailCode::where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $record || ! hash_equals($record->code, $code)) {
            return self::STATUS_INVALID;
        }

        $record->update(['consumed_at' => now()]);

        return self::STATUS_SUCCESS;
    }

    private function send(string $email, string $purpose): bool
    {
        $throttleKey = "email_code_sent:{$purpose}:{$email}";

        if (Cache::has($throttleKey)) {
            return false;
        }

        EmailCode::where('email', $email)->where('purpose', $purpose)->delete();

        $code = (string) random_int(100000, 999999);

        EmailCode::create([
            'email' => $email,
            'purpose' => $purpose,
            'code' => $code,
            'expires_at' => now()->addMinutes(self::CODE_EXPIRY_MINUTES),
        ]);

        $user = User::where('email', $email)->first();
        $recipientName = $user?->name ?? $email;

        $mailable = $purpose === self::PURPOSE_VERIFY_EMAIL
            ? new VerificationCodeMail($recipientName, $code)
            : new PasswordResetCodeMail($recipientName, $code);

        Mail::to($email)->send($mailable);

        Cache::put($throttleKey, true, now()->addSeconds(self::RESEND_THROTTLE_SECONDS));

        return true;
    }
}

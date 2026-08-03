<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function create()
    {
        return view('auth.verify-email');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'array', 'size:6'],
            'code.*' => ['required', 'string', 'size:1', 'digits:1'],
        ]);

        $result = app(AuthService::class)->verifyEmailCode($request->user(), $request->input('code'));

        return match ($result) {
            'messages' => redirect()->intended('/messages'),
            'setup-profile' => redirect()->route('setup-profile'),
            default => back()->withErrors(['code' => $result === 'throttled'
                ? 'Too many attempts. Please wait a minute and try again.'
                : 'Invalid or expired code. Please try again.']),
        };
    }

    public function resend(Request $request)
    {
        $sent = app(AuthService::class)->resendVerification($request->user());

        return back()->with(
            'status',
            $sent ? 'A new code has been sent to your email.' : 'Please wait a minute before requesting a new code.',
        );
    }
}

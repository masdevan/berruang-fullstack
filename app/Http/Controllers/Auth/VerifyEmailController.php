<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailCodeService;
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

        $result = app(EmailCodeService::class)->attemptVerify(
            $request->user()->email,
            EmailCodeService::PURPOSE_VERIFY_EMAIL,
            $request->input('code'),
        );

        return match ($result) {
            EmailCodeService::STATUS_SUCCESS => $this->markEmailVerified($request->user()),
            EmailCodeService::STATUS_INVALID => back()
                ->withErrors(['code' => 'Invalid or expired code. Please try again.']),
            EmailCodeService::STATUS_THROTTLED => back()
                ->withErrors(['code' => 'Too many attempts. Please wait a minute and try again.']),
        };
    }

    public function resend(Request $request)
    {
        $sent = $request->user()->sendEmailVerificationNotification();

        return back()->with(
            'status',
            $sent ? 'A new code has been sent to your email.' : 'Please wait a minute before requesting a new code.',
        );
    }

    private function markEmailVerified(User $user)
    {
        $user->markEmailAsVerified();

        return redirect()->intended('/messages');
    }
}

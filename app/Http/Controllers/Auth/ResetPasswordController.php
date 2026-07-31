<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function create(Request $request)
    {
        if (! $request->session()->has('reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'array', 'size:6'],
            'code.*' => ['required', 'string', 'size:1', 'digits:1'],
        ]);

        $result = app(EmailCodeService::class)->attemptVerify(
            $request->email,
            EmailCodeService::PURPOSE_RESET_PASSWORD,
            $request->input('code'),
        );

        if ($result !== EmailCodeService::STATUS_SUCCESS) {
            $message = $result === EmailCodeService::STATUS_THROTTLED
                ? 'Too many attempts. Please wait a minute and try again.'
                : 'Invalid or expired code. Please try again.';

            return back()->withErrors(['code' => $message]);
        }

        $request->session()->put('reset_email', $request->email);
        $request->session()->put('reset_code_verified', true);

        return redirect()->route('password.reset');
    }

    public function store(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = $request->session()->get('reset_email');

        if (! $email || ! $request->session()->get('reset_code_verified')) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->firstOrFail();

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        $user->setRememberToken(Str::random(60));
        $user->save();

        $request->session()->forget(['reset_email', 'reset_code_verified']);

        return redirect()->route('login')
            ->with('status', 'Password has been reset. Please sign in with your new password.');
    }
}

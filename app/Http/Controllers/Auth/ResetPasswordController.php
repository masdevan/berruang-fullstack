<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;

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

        $error = app(PasswordResetService::class)->verifyCode($request->email, $request->input('code'));

        if ($error) {
            return back()->withErrors(['code' => $error]);
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

        app(PasswordResetService::class)->resetPassword($email, $request->password);

        $request->session()->forget(['reset_email', 'reset_code_verified']);

        return redirect()->route('login')
            ->with('status', 'Password has been reset. Please sign in with your new password.');
    }
}

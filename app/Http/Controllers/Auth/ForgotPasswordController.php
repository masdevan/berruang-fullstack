<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailCodeService;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function sendCode(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        $sent = false;
        if ($user) {
            $sent = app(EmailCodeService::class)->sendPasswordResetCode($user->email);
        }

        $request->session()->put('reset_email', $request->email);
        $request->session()->forget('reset_code_verified');

        if ($sent) {
            $request->session()->flash('status', 'A reset code has been sent to your email.');
        } elseif ($user) {
            $request->session()->flash('error', 'A code was already sent recently. Please wait a minute before requesting another.');
        }

        return redirect()->route('password.reset');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
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

        $result = app(PasswordResetService::class)->sendResetCode($request->email);

        $request->session()->put('reset_email', $request->email);
        $request->session()->forget('reset_code_verified');

        if ($result['sent']) {
            $request->session()->flash('status', 'A reset code has been sent to your email.');
        } elseif ($result['exists']) {
            $request->session()->flash('error', 'A code was already sent recently. Please wait a minute before requesting another.');
        }

        return redirect()->route('password.reset');
    }
}

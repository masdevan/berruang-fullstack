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

        if ($user) {
            app(EmailCodeService::class)->sendPasswordResetCode($user->email);
        }

        $request->session()->put('reset_email', $request->email);
        $request->session()->forget('reset_code_verified');

        return redirect()->route('password.reset');
    }
}

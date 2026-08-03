<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $target = app(AuthService::class)->attemptLogin($credentials, $request->boolean('remember'));

        if ($target === false) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        if ($target === 'verification.notice') {
            return redirect()->route('verification.notice');
        }

        if ($target === 'setup-profile') {
            return redirect()->route('setup-profile');
        }

        return redirect()->intended('/messages');
    }

    public function destroy(Request $request)
    {
        app(AuthService::class)->logout($request);

        return redirect('/');
    }
}

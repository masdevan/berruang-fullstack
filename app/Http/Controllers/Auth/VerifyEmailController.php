<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        return redirect('/')->with('status', 'Email verified successfully.');
    }

    public function resend()
    {
        return back()->with('status', 'A new code has been sent.');
    }
}

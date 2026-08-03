<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class SetupProfileController extends Controller
{
    public function create()
    {
        return view('auth.setup-profile');
    }

    public function store(Request $request)
    {
        if (! $request->boolean('skip')) {
            $request->validate([
                'bio' => ['nullable', 'string', 'max:500', 'regex:/^[^\p{Cc}\p{Zl}\p{Zp}]*$/u'],
                'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
        }

        app(ProfileService::class)->setupProfile(
            $request->user(),
            $request->boolean('skip'),
            $request->input('bio'),
            $request->file('avatar'),
        );

        return redirect()->route('chat');
    }
}

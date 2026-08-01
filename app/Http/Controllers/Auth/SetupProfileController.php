<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SetupProfileController extends Controller
{
    public function create()
    {
        return view('auth.setup-profile');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (! $request->boolean('skip')) {
            $request->validate([
                'bio' => ['nullable', 'string', 'max:500', 'regex:/^[^\p{Cc}\p{Zl}\p{Zp}]*$/u'],
                'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $user->avatar = $request->file('avatar')->store('avatars', 'public');
            }

            $user->bio = $request->input('bio');
        }

        $user->onboarded_at = now();
        $user->save();

        return redirect()->route('chat');
    }
}

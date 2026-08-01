<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    private const USERNAME_CHANGE_INTERVAL_DAYS = 7;

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:500', 'regex:/^[^\p{Cc}\p{Zl}\p{Zp}]*$/u'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $usernameChanged = $request->username !== $user->username;

        if ($usernameChanged) {
            $lastChange = $user->username_changed_at;

            if ($lastChange && $lastChange->gt(now()->subDays(self::USERNAME_CHANGE_INTERVAL_DAYS))) {
                $daysLeft = (int) ceil(now()->diffInDays($lastChange->copy()->addDays(self::USERNAME_CHANGE_INTERVAL_DAYS), false));

                return back()->withErrors([
                    'username' => 'Username can only be changed once every '.self::USERNAME_CHANGE_INTERVAL_DAYS." days. You can change it again in {$daysLeft} day(s).",
                ])->withInput();
            }
        }

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'bio' => $request->bio,
            'username_changed_at' => $usernameChanged ? now() : $user->username_changed_at,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('account_status', 'Account details have been updated.');
    }
}

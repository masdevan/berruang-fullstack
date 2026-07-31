<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        ]);

        $usernameChanged = $request->username !== $user->username;

        if ($usernameChanged) {
            $lastChange = $user->username_changed_at;

            if ($lastChange && $lastChange->gt(now()->subDays(self::USERNAME_CHANGE_INTERVAL_DAYS))) {
                $daysLeft = now()->diffInDays($lastChange->copy()->addDays(self::USERNAME_CHANGE_INTERVAL_DAYS), false);

                return back()->withErrors([
                    'username' => 'Username can only be changed once every '.self::USERNAME_CHANGE_INTERVAL_DAYS." days. You can change it again in {$daysLeft} day(s).",
                ])->withInput();
            }
        }

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'username_changed_at' => $usernameChanged ? now() : $user->username_changed_at,
        ]);

        return back()->with('account_status', 'Account details have been updated.');
    }
}

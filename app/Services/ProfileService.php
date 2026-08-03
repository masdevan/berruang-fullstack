<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public const USERNAME_CHANGE_INTERVAL_DAYS = 7;

    public function setupProfile(User $user, bool $skip, ?string $bio, ?UploadedFile $avatar): void
    {
        if (! $skip) {
            if ($avatar) {
                $this->replaceAvatar($user, $avatar);
            }

            $user->bio = $bio;
        }

        $user->onboarded_at = now();
        $user->save();
    }

    public function updateAccount(User $user, array $data, ?UploadedFile $avatar): ?string
    {
        $usernameChanged = $data['username'] !== $user->username;

        if ($usernameChanged) {
            $lastChange = $user->username_changed_at;

            if ($lastChange && $lastChange->gt(now()->subDays(self::USERNAME_CHANGE_INTERVAL_DAYS))) {
                $daysLeft = (int) ceil(now()->diffInDays($lastChange->copy()->addDays(self::USERNAME_CHANGE_INTERVAL_DAYS), false));

                return 'Username can only be changed once every '.self::USERNAME_CHANGE_INTERVAL_DAYS." days. You can change it again in {$daysLeft} day(s).";
            }
        }

        $payload = [
            'name' => $data['name'],
            'username' => $data['username'],
            'bio' => $data['bio'] ?? null,
            'username_changed_at' => $usernameChanged ? now() : $user->username_changed_at,
        ];

        if ($avatar) {
            $this->replaceAvatar($user, $avatar);
            $payload['avatar'] = $user->avatar;
        }

        $user->update($payload);

        return null;
    }

    public function updatePassword(User $user, string $currentPassword, string $newPassword): ?string
    {
        if (! Hash::check($currentPassword, $user->password)) {
            return 'Current password does not match our records.';
        }

        $user->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();

        return null;
    }

    private function replaceAvatar(User $user, UploadedFile $avatar): void
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = $avatar->store('avatars', 'public');
    }
}

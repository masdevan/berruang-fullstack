<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class WorkspaceService
{
    private const CODE_LENGTH = 8;

    private const CODE_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    public function create(User $owner, string $name): Workspace
    {
        $workspace = Workspace::create([
            'code' => $this->generateCode(),
            'name' => trim($name),
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach($owner->id, ['role' => 'owner']);

        return $workspace;
    }

    public function join(User $user, string $code): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace) {
            return ['ok' => false, 'error' => 'Workspace not found.'];
        }

        if ($workspace->members()->where('user_id', $user->id)->exists()) {
            return ['ok' => false, 'error' => 'You are already a member of this workspace.'];
        }

        $workspace->members()->attach($user->id, ['role' => 'user']);

        return ['ok' => true, 'workspace' => $workspace];
    }

    public function configure(User $user, string $code, ?string $bio, ?string $newCode): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace) {
            return ['ok' => false, 'error' => 'Workspace not found.'];
        }

        $role = $workspace->members()->where('user_id', $user->id)->first()?->pivot->role;

        if (! in_array($role, ['owner', 'admin'], true)) {
            return ['ok' => false, 'error' => 'Only the owner or an admin can configure this workspace.'];
        }

        $finalCode = $workspace->code;
        if ($newCode !== null && trim($newCode) !== '') {
            $candidate = strtoupper(trim($newCode));
            if (! preg_match('/^[A-Z0-9]{8}$/', $candidate)) {
                return ['ok' => false, 'error' => 'Code must be 8 uppercase letters or numbers.'];
            }
            if (Workspace::where('code', $candidate)->where('id', '!=', $workspace->id)->exists()) {
                return ['ok' => false, 'error' => 'That code is already taken.'];
            }
            $finalCode = $candidate;
        }

        $workspace->update([
            'bio' => $bio !== null && trim($bio) !== '' ? trim($bio) : null,
            'code' => $finalCode,
        ]);

        return ['ok' => true, 'workspace' => $workspace];
    }

    public function updateAvatar(Workspace $workspace, UploadedFile $file): void
    {
        if ($workspace->avatar) {
            Storage::disk('public')->delete($workspace->avatar);
            Storage::disk('public')->delete($this->avatarPreviewPath($workspace->avatar));
        }

        $workspace->avatar = $file->store('workspace-avatars', 'public');
        $this->makeAvatarPreview($workspace->avatar);
        $workspace->save();
    }

    public function avatarPreviewPath(string $avatarPath): string
    {
        $dir = pathinfo($avatarPath, PATHINFO_DIRNAME);
        $name = pathinfo($avatarPath, PATHINFO_FILENAME);

        return $dir.'/'.$name.'.preview.webp';
    }

    private function makeAvatarPreview(string $avatarPath): bool
    {
        $src = @imagecreatefromstring((string) Storage::disk('public')->get($avatarPath));
        if (! $src) {
            return false;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $max = 128;
        $scale = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $tmp = tempnam(sys_get_temp_dir(), 'wspv');
        for ($q = 70; $q >= 30; $q -= 5) {
            imagewebp($dst, $tmp, $q);
            if (filesize($tmp) <= 10 * 1024) {
                break;
            }
        }

        imagedestroy($src);
        imagedestroy($dst);

        $stored = Storage::disk('public')->put($this->avatarPreviewPath($avatarPath), file_get_contents($tmp));
        @unlink($tmp);

        return $stored;
    }

    public function members(User $viewer, string $code): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace || ! $workspace->members()->where('user_id', $viewer->id)->exists()) {
            return ['ok' => false];
        }

        $rank = ['owner' => 0, 'admin' => 1, 'user' => 2];

        $members = $workspace->members()
            ->get()
            ->sortBy(fn (User $u) => [$rank[$u->pivot->role] ?? 3, mb_strtolower($u->name)])
            ->values()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'avatar' => $u->avatar ? $u->avatarUrl(36) : $u->initials(),
                'has_avatar' => (bool) $u->avatar,
                'role' => $u->pivot->role,
            ])
            ->all();

        return ['ok' => true, 'members' => $members];
    }

    public function list(User $user): Collection
    {
        return $user->workspaces()->orderBy('name')->get();
    }

    private function generateCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::CODE_CHARS[random_int(0, strlen(self::CODE_CHARS) - 1)];
            }
        } while (Workspace::where('code', $code)->exists());

        return $code;
    }
}

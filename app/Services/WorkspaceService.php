<?php

namespace App\Services;

use App\Events\WorkspaceInvitation;
use App\Events\WorkspaceInviteResponse;
use App\Events\WorkspaceMemberRemoved;
use App\Events\WorkspaceMembersChanged;
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

        broadcast(new WorkspaceMembersChanged($workspace));

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

        if (! $workspace || ! $workspace->activeMembers()->where('user_id', $viewer->id)->exists()) {
            return ['ok' => false];
        }

        $rank = ['owner' => 0, 'admin' => 1, 'user' => 2];

        $members = $workspace->activeMembers()
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
                'creator' => $u->id === $workspace->owner_id,
                'is_me' => $u->id === $viewer->id,
                'bio' => $u->bio ?? '',
                'joined' => $u->pivot->created_at?->format('d M Y') ?? '',
            ])
            ->all();

        return ['ok' => true, 'members' => $members];
    }

    public function invite(User $actor, string $code, string $identifier): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace) {
            return ['ok' => false, 'error' => 'Workspace not found.'];
        }

        $role = $workspace->members()->where('user_id', $actor->id)->first()?->pivot->role;

        if (! in_array($role, ['owner', 'admin'], true)) {
            return ['ok' => false, 'error' => 'Only the owner or an admin can add members.'];
        }

        $target = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $target) {
            return ['ok' => false, 'error' => 'User not found.'];
        }

        $existing = $workspace->members()->where('user_id', $target->id)->first();

        if ($existing) {
            return ['ok' => false, 'error' => $existing->pivot->status === 'pending'
                ? 'This user has already been invited.'
                : 'This user is already a member.'];
        }

        $workspace->members()->attach($target->id, [
            'role' => 'user',
            'status' => 'pending',
            'inviter_id' => $actor->id,
        ]);

        broadcast(new WorkspaceInvitation($workspace, $target, $actor));

        return ['ok' => true];
    }

    public function respondInvite(User $user, string $code, bool $accept): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace) {
            return ['ok' => false, 'error' => 'Workspace not found.'];
        }

        $pivot = $workspace->members()->where('user_id', $user->id)->first();

        if (! $pivot || $pivot->pivot->status !== 'pending') {
            return ['ok' => false, 'error' => 'No pending invitation.'];
        }

        if ($accept) {
            $workspace->members()->updateExistingPivot($user->id, ['status' => 'member']);
            broadcast(new WorkspaceMembersChanged($workspace));
        } else {
            $workspace->members()->detach($user->id);
        }

        broadcast(new WorkspaceInviteResponse($workspace, $user, $accept));

        return ['ok' => true];
    }

    public function promote(User $actor, string $code, int $userId): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace || ! $this->isManager($workspace, $actor)) {
            return ['ok' => false, 'error' => 'Only an owner can manage members.'];
        }

        $target = $workspace->activeMembers()->where('user_id', $userId)->first();

        if (! $target) {
            return ['ok' => false, 'error' => 'Member not found.'];
        }

        if ($target->pivot->role === 'owner') {
            return ['ok' => false, 'error' => 'This user is already an owner.'];
        }

        $workspace->members()->updateExistingPivot($target->id, ['role' => 'owner']);
        broadcast(new WorkspaceMembersChanged($workspace));

        return ['ok' => true];
    }

    public function demote(User $actor, string $code, int $userId): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace || ! $this->isManager($workspace, $actor)) {
            return ['ok' => false, 'error' => 'Only an owner can manage members.'];
        }

        $target = $workspace->activeMembers()->where('user_id', $userId)->first();

        if (! $target) {
            return ['ok' => false, 'error' => 'Member not found.'];
        }

        if ($target->id === $workspace->owner_id) {
            return ['ok' => false, 'error' => 'The workspace creator cannot be demoted.'];
        }

        if ($target->pivot->role !== 'owner') {
            return ['ok' => false, 'error' => 'This user is not an owner.'];
        }

        $workspace->members()->updateExistingPivot($target->id, ['role' => 'user']);
        broadcast(new WorkspaceMembersChanged($workspace));

        return ['ok' => true];
    }

    public function kick(User $actor, string $code, array $userIds): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace || ! $this->isManager($workspace, $actor)) {
            return ['ok' => false, 'error' => 'Only an owner can manage members.'];
        }

        $targets = $workspace->activeMembers()
            ->whereIn('users.id', array_values(array_unique($userIds)))
            ->get();

        if ($targets->isEmpty()) {
            return ['ok' => false, 'error' => 'No members to remove.'];
        }

        $removed = [];
        foreach ($targets as $target) {
            if ($target->id === $workspace->owner_id || $target->id === $actor->id) {
                continue;
            }
            $removed[] = $target;
        }

        if (empty($removed)) {
            return ['ok' => false, 'error' => 'No members to remove.'];
        }

        $removedIds = array_map(fn (User $u) => $u->id, $removed);
        $workspace->members()->detach($removedIds);

        foreach ($removed as $target) {
            broadcast(new WorkspaceMemberRemoved($workspace, $target));
        }

        broadcast(new WorkspaceMembersChanged($workspace));

        return ['ok' => true];
    }

    public function leave(User $user, string $code, ?int $successorId): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace) {
            return ['ok' => false, 'error' => 'Workspace not found.'];
        }

        if (! $workspace->activeMembers()->where('user_id', $user->id)->exists()) {
            return ['ok' => false, 'error' => 'You are not a member of this workspace.'];
        }

        if ($workspace->owner_id === $user->id) {
            if (! $successorId) {
                return ['ok' => false, 'error' => 'You must delegate ownership before leaving.'];
            }

            $successor = $workspace->activeMembers()->where('user_id', $successorId)->first();

            if (! $successor || $successor->id === $user->id) {
                return ['ok' => false, 'error' => 'Choose a valid member to delegate ownership to.'];
            }

            if ($successor->pivot->role !== 'owner') {
                $workspace->members()->updateExistingPivot($successor->id, ['role' => 'owner']);
            }

            $workspace->update(['owner_id' => $successor->id]);
            $workspace->members()->detach($user->id);

            broadcast(new WorkspaceMembersChanged($workspace));

            return ['ok' => true];
        }

        $workspace->members()->detach($user->id);
        broadcast(new WorkspaceMembersChanged($workspace));

        return ['ok' => true];
    }

    public function list(User $user): Collection
    {
        return $user->workspaces()->orderBy('name')->get();
    }

    private function isManager(Workspace $workspace, User $user): bool
    {
        $role = $workspace->members()->where('user_id', $user->id)->first()?->pivot->role;

        return in_array($role, ['owner', 'admin'], true);
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

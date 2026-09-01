<?php

namespace App\Services;

use App\Events\WorkspaceInvitation;
use App\Events\WorkspaceInviteResponse;
use App\Events\WorkspaceMemberRemoved;
use App\Events\WorkspaceMembersChanged;
use App\Events\WorkspaceMessageSent;
use App\Events\WorkspaceMessagesRead;
use App\Events\WorkspaceTyping;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class WorkspaceService
{
    private const CODE_LENGTH = 8;

    private const CODE_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    public function create(User $owner, string $name, ?string $code = null): Workspace
    {
        $workspace = Workspace::create([
            'code' => $code ?: $this->generateCode(),
            'name' => trim($name),
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach($owner->id, ['role' => 'owner']);

        return $workspace;
    }

    public function createWithDetails(User $owner, string $name, ?string $code, ?string $bio, ?UploadedFile $avatar, array $identifiers): array
    {
        $finalCode = null;
        if ($code !== null && trim($code) !== '') {
            $candidate = strtoupper(trim($code));
            if (! preg_match('/^[A-Z0-9]{8}$/', $candidate)) {
                return ['ok' => false, 'error' => 'Code must be 8 uppercase letters or numbers.'];
            }
            if (Workspace::where('code', $candidate)->exists()) {
                return ['ok' => false, 'error' => 'That code is already taken.'];
            }
            $finalCode = $candidate;
        }

        $targets = [];
        foreach (array_values(array_filter(array_map('trim', $identifiers))) as $identifier) {
            $identifier = ltrim($identifier, '@');
            $target = User::where('username', $identifier)->orWhere('email', $identifier)->first();
            if (! $target) {
                return ['ok' => false, 'error' => "User '{$identifier}' not found."];
            }
            if ($target->id === $owner->id) {
                return ['ok' => false, 'error' => 'You cannot invite yourself.'];
            }
            $targets[] = $target;
        }

        $workspace = $this->create($owner, $name, $finalCode);

        $workspace->update([
            'bio' => $bio !== null && trim($bio) !== '' ? trim($bio) : null,
        ]);

        if ($avatar) {
            $this->updateAvatar($workspace, $avatar);
        }

        foreach ($targets as $target) {
            $this->invite($owner, $workspace->code, $target->username);
        }

        return ['ok' => true, 'workspace' => $workspace];
    }

    public function join(User $user, string $code): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace) {
            return ['ok' => false, 'error' => 'Workspace not found.'];
        }

        $pivot = $workspace->members()->where('user_id', $user->id)->first();

        if ($pivot) {
            if ($pivot->pivot->status === 'kicked') {
                return ['ok' => false, 'error' => 'You have been removed from this workspace. You can only rejoin if you are invited again.'];
            }

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

        $role = $workspace->activeMembers()->where('user_id', $user->id)->first()?->pivot->role;

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
                'last_read_message_id' => (int) ($u->pivot->last_read_message_id ?? 0),
            ])
            ->all();

        return ['ok' => true, 'members' => $members];
    }

    public function invite(User $actor, string $code, string $identifier): array
    {
        $identifier = ltrim(trim($identifier), '@');
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
            if ($existing->pivot->status === 'kicked') {
                $workspace->members()->updateExistingPivot($target->id, [
                    'role' => 'user',
                    'status' => 'pending',
                    'inviter_id' => $actor->id,
                ]);

                broadcast(new WorkspaceInvitation($workspace, $target, $actor));

                return ['ok' => true];
            }

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

        return ['ok' => true, 'workspace' => $workspace];
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

        foreach ($removedIds as $id) {
            $workspace->members()->updateExistingPivot($id, ['status' => 'kicked']);
        }

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
            $hasOtherMembers = $workspace->activeMembers()->where('user_id', '!=', $user->id)->exists();

            if (! $hasOtherMembers) {
                $workspace->delete();

                return ['ok' => true];
            }

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
        return $user->workspaces()
            ->wherePivot('status', '!=', 'kicked')
            ->orderBy('name')
            ->get();
    }

    public function listMeta(User $user, Collection $workspaces): array
    {
        $meta = [];
        foreach ($workspaces as $workspace) {
            $last = WorkspaceMessage::with('sender')
                ->where('workspace_id', $workspace->id)
                ->latest('id')
                ->first();

            $unread = 0;
            $lastRead = $workspace->pivot->last_read_message_id;
            if ($last) {
                $unread = WorkspaceMessage::where('workspace_id', $workspace->id)
                    ->where('id', '>', (int) ($lastRead ?? 0))
                    ->count();
            }

            $meta[$workspace->id] = [
                'last' => $last ? $this->messageLabel($last) : '',
                'time' => $last ? $last->created_at->format('H:i') : '',
                'sender' => $last ? $last->sender->name : '',
                'unread' => (int) $unread,
                'sent' => $last ? $last->sender_id === $user->id : false,
            ];
        }

        return $meta;
    }

    public function messages(User $viewer, string $code, int $after = 0, int $before = 0): ?array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace || ! $workspace->activeMembers()->where('user_id', $viewer->id)->exists()) {
            return null;
        }

        $query = WorkspaceMessage::with(['sender', 'workspace'])->where('workspace_id', $workspace->id);

        if ($after > 0) {
            $messages = $query->where('id', '>', $after)->orderBy('id')->get();

            return ['messages' => $messages->map(fn (WorkspaceMessage $m) => $this->messagePayload($m, $viewer))->all(), 'has_more' => false];
        }

        if ($before > 0) {
            $query->where('id', '<', $before);
        }

        $batch = $query->orderByDesc('id')->limit(26)->get()->reverse()->values();
        $hasMore = $batch->count() > 25;
        $messages = $hasMore ? $batch->slice(-25)->values() : $batch;

        return [
            'messages' => $messages->map(fn (WorkspaceMessage $m) => $this->messagePayload($m, $viewer))->all(),
            'has_more' => $hasMore,
        ];
    }

    public function storeMessage(User $sender, string $code, ?string $body, ?UploadedFile $file, ?string $type): array
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace || ! $workspace->activeMembers()->where('user_id', $sender->id)->exists()) {
            return ['ok' => false, 'error' => 'You are not a member of this workspace.'];
        }

        $resolvedType = $type ?: ($file ? 'document' : 'text');
        $mime = null;

        if ($file) {
            $mime = $file->getMimeType();
            $resolvedType = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : 'document');

            if (! in_array($mime, array_merge(ChatService::ALLOWED_MEDIA_MIMES, ChatService::DOCUMENT_MIMES), true)) {
                return ['ok' => false, 'error' => 'File type not allowed.'];
            }
        }

        $dimensions = null;
        if ($file && $resolvedType === 'image') {
            $size = @getimagesize($file->getRealPath());
            if (is_array($size)) {
                $dimensions = ['width' => $size[0], 'height' => $size[1]];
            }
        }

        $filePath = $file
            ? $file->storeAs('uploads', ($resolvedType === 'document' ? uniqid().'-' : '').$file->getClientOriginalName(), 'public')
            : null;

        $previewPath = null;
        if ($file && $resolvedType === 'image' && $dimensions && $mime !== 'image/svg+xml') {
            $previewPath = app(ChatService::class)->previewPathFor($filePath);
            if (! app(ChatService::class)->createImagePreview($file->getRealPath(), $mime, 10 * 1024, $previewPath)) {
                $previewPath = null;
            }
        }

        $message = WorkspaceMessage::create([
            'workspace_id' => $workspace->id,
            'sender_id' => $sender->id,
            'body' => $body ?: ($file ? $file->getClientOriginalName() : ''),
            'type' => $resolvedType,
            'file_path' => $filePath,
            'preview_path' => $previewPath,
            ...($dimensions ?? []),
        ]);

        broadcast(new WorkspaceMessageSent($message));

        return ['ok' => true, 'message' => $message];
    }

    public function markRead(User $user, string $code): void
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace || ! $workspace->members()->where('user_id', $user->id)->exists()) {
            return;
        }

        $max = WorkspaceMessage::where('workspace_id', $workspace->id)->max('id');

        if ($max) {
            $workspace->members()->updateExistingPivot($user->id, ['last_read_message_id' => $max]);
            broadcast(new WorkspaceMessagesRead($workspace, $user->id, (int) $max));
        }
    }

    public function broadcastTyping(User $sender, string $code, bool $typing): bool
    {
        $workspace = Workspace::where('code', strtoupper(trim($code)))->first();

        if (! $workspace || ! $workspace->activeMembers()->where('user_id', $sender->id)->exists()) {
            return false;
        }

        broadcast(new WorkspaceTyping($workspace, $sender->id, $sender->username, $sender->name, $typing));

        return true;
    }

    private function messageLabel(WorkspaceMessage $m): string
    {
        return match ($m->type) {
            'image' => 'Photo',
            'video' => 'Video',
            'document' => 'Document',
            default => $m->body,
        };
    }

    private function messagePayload(WorkspaceMessage $m, User $viewer): array
    {
        $data = [
            'id' => $m->id,
            'body' => $m->body,
            'time' => $m->created_at->format('H:i'),
            'from' => $m->sender_id === $viewer->id ? 'me' : 'other',
            'type' => $m->type,
            'sender_name' => $m->sender->name,
            'sender_username' => $m->sender->username,
            'file' => $m->file_path
                ? [
                    'url' => $m->fileUrl(),
                    'preview_url' => $m->preview_path ? asset('storage/'.$m->preview_path) : null,
                    'name' => $m->fileName(),
                    'width' => $m->width,
                    'height' => $m->height,
                ]
                : null,
        ];

        if ($data['from'] === 'me') {
            $data['read'] = $this->isReadByAll($m->workspace, $m);
        }

        return $data;
    }

    private function isReadByAll(Workspace $workspace, WorkspaceMessage $m): bool
    {
        $others = $workspace->activeMembers()->where('users.id', '!=', $m->sender_id)->get();

        if ($others->isEmpty()) {
            return true;
        }

        return $others->every(fn (User $u) => (int) $u->pivot->last_read_message_id >= $m->id);
    }

    private function isManager(Workspace $workspace, User $user): bool
    {
        $role = $workspace->activeMembers()->where('user_id', $user->id)->first()?->pivot->role;

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

<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Collection;

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

        $workspace->members()->attach($owner->id);

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

        $workspace->members()->attach($user->id);

        return ['ok' => true, 'workspace' => $workspace];
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

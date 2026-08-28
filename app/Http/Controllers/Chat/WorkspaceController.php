<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Services\WorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function __construct(private readonly WorkspaceService $workspaces) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $workspace = $this->workspaces->create($request->user(), $request->name);

        return response()->json([
            'id' => $workspace->id,
            'code' => $workspace->code,
            'html' => $this->listHtml($request->user()),
        ]);
    }

    public function join(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:8', 'regex:/^[A-Z0-9]+$/i'],
        ]);

        $result = $this->workspaces->join($request->user(), $request->code);

        if (! $result['ok']) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json([
            'id' => $result['workspace']->id,
            'code' => $result['workspace']->code,
            'html' => $this->listHtml($request->user()),
        ]);
    }

    public function members(Request $request, string $code): JsonResponse
    {
        $result = $this->workspaces->members($request->user(), $code);

        if (! $result['ok']) {
            return response()->json(['message' => 'Workspace not found.'], 404);
        }

        return response()->json($result['members']);
    }

    public function inviteMember(Request $request, string $code): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->workspaces->invite($request->user(), $code, trim($request->identifier));

        if (! $result['ok']) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function respondInvite(Request $request, string $code): JsonResponse
    {
        $request->validate([
            'accept' => ['required', 'boolean'],
        ]);

        $result = $this->workspaces->respondInvite($request->user(), $code, (bool) $request->accept);

        if (! $result['ok']) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json([
            'ok' => true,
            'code' => $result['workspace']->code,
            'html' => $this->listHtml($request->user()),
        ]);
    }

    public function promoteMember(Request $request, string $code, int $id): JsonResponse
    {
        $result = $this->workspaces->promote($request->user(), $code, $id);

        if (! $result['ok']) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function demoteMember(Request $request, string $code, int $id): JsonResponse
    {
        $result = $this->workspaces->demote($request->user(), $code, $id);

        if (! $result['ok']) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function kickMembers(Request $request, string $code): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->workspaces->kick($request->user(), $code, $request->input('ids'));

        if (! $result['ok']) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function leave(Request $request, string $code): JsonResponse
    {
        $request->validate([
            'successor_id' => ['nullable', 'integer'],
        ]);

        $result = $this->workspaces->leave(
            $request->user(),
            $code,
            $request->has('successor_id') ? (int) $request->successor_id : null,
        );

        if (! $result['ok']) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json([
            'ok' => true,
            'html' => $this->listHtml($request->user()),
        ]);
    }

    public function configure(Request $request, string $code): JsonResponse
    {
        $request->validate([
            'bio' => ['nullable', 'string', 'max:500'],
            'code' => ['nullable', 'string', 'max:8', 'regex:/^[A-Z0-9]+$/i'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $result = $this->workspaces->configure($request->user(), $code, $request->input('bio'), $request->input('code'));

        if (! $result['ok']) {
            return response()->json(['message' => $result['error']], 422);
        }

        $workspace = $result['workspace'];

        if ($request->hasFile('avatar')) {
            $this->workspaces->updateAvatar($workspace, $request->file('avatar'));
        }

        return response()->json([
            'code' => $workspace->code,
            'bio' => $workspace->bio,
            'avatar' => $workspace->avatar ? $workspace->avatarPreviewUrl() : '',
            'html' => $this->listHtml($request->user()),
        ]);
    }

    private function listHtml($user): string
    {
        return view('components.chat.workspace-list', [
            'workspaces' => $this->workspaces->list($user),
        ])->render();
    }
}

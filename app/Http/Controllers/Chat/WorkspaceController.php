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

    private function listHtml($user): string
    {
        return view('components.chat.workspace-list', [
            'workspaces' => $this->workspaces->list($user),
        ])->render();
    }
}

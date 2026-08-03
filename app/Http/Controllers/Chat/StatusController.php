<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function index(Request $request): JsonResponse
    {
        $usernames = collect(explode(',', (string) $request->query('users')))->filter()->values()->all();

        return response()->json($this->chat->presenceStatuses($usernames));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:online,idle']);

        $this->chat->setPresence($request->user(), $data['status']);

        return response()->json(['ok' => true]);
    }
}

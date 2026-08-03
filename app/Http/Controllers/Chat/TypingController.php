<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypingController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'to' => ['required', 'string'],
            'typing' => ['required', 'boolean'],
        ]);

        if (! $this->chat->broadcastTyping($request->user(), $request->to, (bool) $request->typing)) {
            return response()->json(['message' => 'User not found.'], 422);
        }

        return response()->json(['ok' => true]);
    }
}

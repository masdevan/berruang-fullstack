<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['with' => ['required', 'string']]);

        $messages = $this->chat->thread($request->user(), $request->with, (int) $request->input('after'));

        if ($messages === null) {
            return response()->json(['message' => 'Contact not found.'], 422);
        }

        return response()->json(['messages' => $messages]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $request->validate(['with' => ['required', 'string']]);

        $this->chat->markRead($request->user(), $request->with);

        return response()->json(['ok' => true]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'to' => ['required', 'string'],
            'body' => ['required_without:file', 'nullable', 'string', 'max:5000'],
            'type' => ['nullable', 'string', Rule::in(Message::TYPES)],
            'file' => ['nullable', 'file', 'max:20480'],
        ]);

        $result = $this->chat->store(
            $request->user(),
            $request->to,
            $request->body,
            $request->file('file'),
            $request->type,
        );

        if (! $result['ok']) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json($this->chat->storedPayload($result['message']));
    }
}

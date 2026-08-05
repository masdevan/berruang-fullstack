<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use App\Services\WorkspaceService;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chat,
        private readonly WorkspaceService $workspaces,
    ) {}

    public function index()
    {
        $users = auth()->user()->contacts()->orderBy('first_name')->limit(20)->get();

        return view('chat.index', [
            'users' => $users,
            'meta' => $this->chat->conversationMeta(auth()->user(), $users),
            'drafts' => $this->chat->drafts(),
            'workspaces' => $this->workspaces->list(auth()->user()),
        ]);
    }
}

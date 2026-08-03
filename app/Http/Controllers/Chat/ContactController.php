<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    private const PER_PAGE = 20;

    public function __construct(private readonly ChatService $chat) {}

    public function checkUsername(string $username): JsonResponse
    {
        return response()->json(['taken' => User::where('username', $username)->exists()]);
    }

    public function index(Request $request): JsonResponse
    {
        $contacts = $request->user()->contacts()
            ->orderBy('first_name')
            ->paginate(self::PER_PAGE);

        $html = view('components.chat.conversation-list-items', [
            'users' => $contacts->items(),
            'meta' => $this->chat->conversationMeta($request->user(), $contacts->getCollection()),
            'drafts' => $this->chat->drafts(),
        ])->render();

        return response()->json([
            'html' => $html,
            'has_more' => $contacts->hasMorePages(),
            'next_page' => $contacts->currentPage() + 1,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $result = $this->chat->addContact($request->user(), trim((string) $request->input('username')));

        if (! $result['ok']) {
            return response()->json(['message' => $result['error']], 422);
        }

        $html = view('components.chat.conversation-list-items', [
            'users' => collect([$result['target']]),
            'meta' => $this->chat->conversationMeta($request->user(), collect([$result['target']])),
        ])->render();

        return response()->json([
            'id' => $result['target']->id,
            'html' => $html,
        ]);
    }

    public function updateNames(Request $request, int $id): JsonResponse
    {
        $result = $this->chat->updateContactNames(
            $request->user(),
            $id,
            trim((string) $request->input('first_name')),
            trim((string) $request->input('last_name')),
        );

        if (! $result['ok']) {
            return response()->json(['message' => 'Contact not found.'], 404);
        }

        $html = view('components.chat.conversation-list-items', [
            'users' => collect([$result['contact']]),
            'meta' => $this->chat->conversationMeta($request->user(), collect([$result['contact']])),
        ])->render();

        return response()->json(['html' => $html]);
    }
}

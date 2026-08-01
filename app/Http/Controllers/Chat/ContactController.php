<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): JsonResponse
    {
        $contacts = $request->user()->contacts()
            ->orderBy('name')
            ->paginate(self::PER_PAGE);

        $html = view('components.chat.conversation-list-items', [
            'users' => $contacts->items(),
            'onlineIds' => $this->onlineIds(),
        ])->render();

        return response()->json([
            'html' => $html,
            'has_more' => $contacts->hasMorePages(),
            'next_page' => $contacts->currentPage() + 1,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $username = trim((string) $request->input('username'));

        $target = $username !== ''
            ? User::where('username', $username)->first()
            : null;

        if (! $target) {
            return response()->json(['message' => 'User not found.'], 422);
        }

        if ($target->is($request->user())) {
            return response()->json(['message' => 'You cannot add yourself.'], 422);
        }

        if ($request->user()->contacts()->where('contact_user_id', $target->id)->exists()) {
            return response()->json(['message' => 'This user is already in your contacts.'], 422);
        }

        try {
            $request->user()->contacts()->attach($target->id);
        } catch (QueryException) {
            return response()->json(['message' => 'This user is already in your contacts.'], 422);
        }

        $html = view('components.chat.conversation-list-items', [
            'users' => collect([$target]),
            'onlineIds' => $this->onlineIds(),
        ])->render();

        return response()->json([
            'id' => $target->id,
            'html' => $html,
        ]);
    }

    public function updateNames(Request $request, int $id): JsonResponse
    {
        $first = trim((string) $request->input('first_name'));
        $last = trim((string) $request->input('last_name'));

        $contact = $request->user()->contacts()
            ->where('contact_user_id', $id)
            ->first();

        if (! $contact) {
            return response()->json(['message' => 'Contact not found.'], 404);
        }

        $contact->pivot->update([
            'first_name' => $first !== '' ? $first : null,
            'last_name' => $last !== '' ? $last : null,
        ]);

        $html = view('components.chat.conversation-list-items', [
            'users' => collect([$contact]),
            'onlineIds' => $this->onlineIds(),
        ])->render();

        return response()->json(['html' => $html]);
    }

    private function onlineIds(): array
    {
        return DB::table('sessions')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->all();
    }
}

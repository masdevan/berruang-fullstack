<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ContactController extends Controller
{
    private const PER_PAGE = 20;

    public static function drafts(): array
    {
        return collect(session()->all())
            ->filter(fn ($value, $key) => str_starts_with($key, 'chat_draft:'))
            ->mapWithKeys(fn ($value, $key) => [str_replace('chat_draft:', '', $key) => $value])
            ->all();
    }

    public static function conversationMeta(User $user, Collection $contacts): array
    {
        $ids = $contacts->pluck('id');

        $lastMessages = Message::where(function ($q) use ($user, $ids) {
            $q->where('sender_id', $user->id)->whereIn('receiver_id', $ids)
                ->orWhere(function ($q2) use ($user, $ids) {
                    $q2->whereIn('sender_id', $ids)->where('receiver_id', $user->id);
                });
        })->orderByDesc('id')->limit(500)->get()->groupBy(function (Message $m) use ($user) {
            return $m->sender_id === $user->id ? $m->receiver_id : $m->sender_id;
        });

        $unread = Message::where('receiver_id', $user->id)
            ->whereIn('sender_id', $ids)
            ->whereNull('read_at')
            ->selectRaw('sender_id, count(*) as total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $meta = [];
        foreach ($contacts as $contact) {
            $last = $lastMessages->get($contact->id)?->first();
            $meta[$contact->id] = [
                'last' => $last ? self::previewLabel($last) : '',
                'time' => $last ? $last->created_at->format('H:i') : '',
                'unread' => (int) ($unread[$contact->id] ?? 0),
            ];
        }

        return $meta;
    }

    private static function previewLabel(Message $m): string
    {
        return match ($m->type) {
            'image' => 'Photo',
            'video' => 'Video',
            'document' => 'Document',
            default => $m->body,
        };
    }

    public function index(Request $request): JsonResponse
    {
        $contacts = $request->user()->contacts()
            ->orderBy('first_name')
            ->paginate(self::PER_PAGE);

        $html = view('components.chat.conversation-list-items', [
            'users' => $contacts->items(),
            'meta' => self::conversationMeta($request->user(), $contacts->getCollection()),
            'drafts' => self::drafts(),
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
            'meta' => self::conversationMeta($request->user(), collect([$target])),
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
            'meta' => self::conversationMeta($request->user(), collect([$contact])),
        ])->render();

        return response()->json(['html' => $html]);
    }
}

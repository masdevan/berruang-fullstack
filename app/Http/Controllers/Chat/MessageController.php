<?php

namespace App\Http\Controllers\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    private const HISTORY_LIMIT = 50;

    public function index(Request $request): JsonResponse
    {
        $request->validate(['with' => ['required', 'string']]);

        $user = $request->user();
        $other = User::where('username', $request->with)->first();

        $isContact = $other && $user->contacts()->where('contact_user_id', $other->id)->exists();
        $hasThread = $other && Message::where(function ($q) use ($user, $other) {
            $q->where('sender_id', $user->id)->where('receiver_id', $other->id)
                ->orWhere(function ($q2) use ($user, $other) {
                    $q2->where('sender_id', $other->id)->where('receiver_id', $user->id);
                });
        })->exists();

        if (! $isContact && ! $hasThread) {
            return response()->json(['message' => 'Contact not found.'], 422);
        }

        $query = Message::with('sender')->where(function ($q) use ($user, $other) {
            $q->where('sender_id', $user->id)->where('receiver_id', $other->id)
                ->orWhere(function ($q2) use ($user, $other) {
                    $q2->where('sender_id', $other->id)->where('receiver_id', $user->id);
                });
        });

        Message::where('sender_id', $other->id)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($after = (int) $request->input('after')) {
            $messages = $query->where('id', '>', $after)->orderBy('id')->get();
        } else {
            $messages = $query->orderByDesc('id')->limit(self::HISTORY_LIMIT)->get()->reverse()->values();
        }

        $contactNames = [];

        return response()->json([
            'messages' => $messages->map(function (Message $m) use ($user) {
                $data = [
                    'id' => $m->id,
                    'body' => $m->body,
                    'time' => $m->created_at->format('H:i'),
                    'from' => $m->sender_id === $user->id ? 'me' : 'other',
                ];

                if ($data['from'] === 'other') {
                    $data += $m->senderDisplayFor($user);
                }

                return $data;
            }),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'to' => ['required', 'string'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $receiver = User::where('username', $request->to)->first();

        if (! $receiver || ! $user->contacts()->where('contact_user_id', $receiver->id)->exists()) {
            return response()->json(['message' => 'Contact not found.'], 422);
        }

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'body' => $request->body,
        ]);

        if (! $receiver->contacts()->where('contact_user_id', $user->id)->exists()) {
            $receiver->contacts()->attach($user->id);
        }

        broadcast(new MessageSent($message));

        return response()->json([
            'id' => $message->id,
            'time' => $message->created_at->format('H:i'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Chat;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    private const HISTORY_LIMIT = 50;

    private const DOCUMENT_MIMES = [
        'application/pdf', 'text/plain', 'application/zip',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/csv',
    ];

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

        $unreadIds = Message::where('sender_id', $other->id)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->pluck('id');

        if ($unreadIds->isNotEmpty()) {
            Message::whereIn('id', $unreadIds)->update(['read_at' => now()]);
            broadcast(new MessageRead($other, $user, $unreadIds->all()));
        }

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
                    'type' => $m->type,
                    'read_at' => $m->read_at,
                    'file' => $m->file_path
                        ? ['url' => $m->fileUrl(), 'name' => $m->fileName()]
                        : null,
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
            'body' => ['required_without:file', 'nullable', 'string', 'max:5000'],
            'type' => ['nullable', 'string', Rule::in(Message::TYPES)],
            'file' => ['nullable', 'file', 'max:20480'],
        ]);

        $user = $request->user();
        $receiver = User::where('username', $request->to)->first();

        if (! $receiver || ! $user->contacts()->where('contact_user_id', $receiver->id)->exists()) {
            return response()->json(['message' => 'Contact not found.'], 422);
        }

        $type = $request->type ?: ($request->hasFile('file') ? 'document' : 'text');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $mime = $file->getMimeType();
            $type = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : 'document');

            if (! in_array($mime, array_merge(
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
                ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'],
                self::DOCUMENT_MIMES
            ))) {
                return response()->json(['message' => 'File type not allowed.'], 422);
            }
        }

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'body' => $request->body ?: ($request->hasFile('file') ? $file->getClientOriginalName() : ''),
            'type' => $type,
            'file_path' => $request->hasFile('file')
                ? $file->storeAs('uploads', ($type === 'document' ? uniqid().'-' : '').$file->getClientOriginalName(), 'public')
                : null,
        ]);

        if (! $receiver->contacts()->where('contact_user_id', $user->id)->exists()) {
            $receiver->contacts()->attach($user->id);
        }

        broadcast(new MessageSent($message));

        return response()->json([
            'id' => $message->id,
            'time' => $message->created_at->format('H:i'),
            'type' => $message->type,
            'file' => $message->file_path
                ? ['url' => $message->fileUrl(), 'name' => $message->fileName()]
                : null,
        ]);
    }
}

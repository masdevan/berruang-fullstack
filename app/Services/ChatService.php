<?php

namespace App\Services;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\TypingEvent;
use App\Events\UserStatusChanged;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ChatService
{
    private const HISTORY_LIMIT = 50;

    private const DOCUMENT_MIMES = [
        'application/pdf', 'text/plain', 'application/zip',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/csv',
    ];

    private const ALLOWED_MEDIA_MIMES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime',
    ];

    public function thread(User $viewer, string $with, int $after = 0): ?array
    {
        $other = User::where('username', $with)->first();

        if (! $other || ! $this->canOpenThread($viewer, $other)) {
            return null;
        }

        $unreadIds = Message::where('sender_id', $other->id)
            ->where('receiver_id', $viewer->id)
            ->whereNull('read_at')
            ->pluck('id');

        if ($unreadIds->isNotEmpty()) {
            Message::whereIn('id', $unreadIds)->update(['read_at' => now()]);
            broadcast(new MessageRead($other, $viewer, $unreadIds->all()));
        }

        $query = Message::with('sender')->where(function ($q) use ($viewer, $other) {
            $q->where('sender_id', $viewer->id)->where('receiver_id', $other->id)
                ->orWhere(function ($q2) use ($viewer, $other) {
                    $q2->where('sender_id', $other->id)->where('receiver_id', $viewer->id);
                });
        });

        if ($after > 0) {
            $messages = $query->where('id', '>', $after)->orderBy('id')->get();
        } else {
            $messages = $query->orderByDesc('id')->limit(self::HISTORY_LIMIT)->get()->reverse()->values();
        }

        return $messages->map(fn (Message $m) => $this->messagePayload($m, $viewer))->all();
    }

    public function markRead(User $reader, string $with): void
    {
        $other = User::where('username', $with)->first();

        if (! $other) {
            return;
        }

        $unreadIds = Message::where('sender_id', $other->id)
            ->where('receiver_id', $reader->id)
            ->whereNull('read_at')
            ->pluck('id');

        if ($unreadIds->isNotEmpty()) {
            Message::whereIn('id', $unreadIds)->update(['read_at' => now()]);
            broadcast(new MessageRead($other, $reader, $unreadIds->all()));
        }
    }

    public function store(User $sender, string $to, ?string $body, ?UploadedFile $file, ?string $type): array
    {
        $receiver = User::where('username', $to)->first();

        if (! $receiver || ! $sender->contacts()->where('contact_user_id', $receiver->id)->exists()) {
            return ['ok' => false, 'error' => 'Contact not found.'];
        }

        $resolvedType = $type ?: ($file ? 'document' : 'text');
        $mime = null;

        if ($file) {
            $mime = $file->getMimeType();
            $resolvedType = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : 'document');

            if (! in_array($mime, array_merge(self::ALLOWED_MEDIA_MIMES, self::DOCUMENT_MIMES))) {
                return ['ok' => false, 'error' => 'File type not allowed.'];
            }
        }

        $dimensions = null;
        if ($file && $resolvedType === 'image') {
            $dimensions = $this->optimizeImage($file->getRealPath(), $mime);
        }

        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'body' => $body ?: ($file ? $file->getClientOriginalName() : ''),
            'type' => $resolvedType,
            'file_path' => $file
                ? $file->storeAs('uploads', ($resolvedType === 'document' ? uniqid().'-' : '').$file->getClientOriginalName(), 'public')
                : null,
            ...($dimensions ?? []),
        ]);

        if (! $receiver->contacts()->where('contact_user_id', $sender->id)->exists()) {
            $receiver->contacts()->attach($sender->id);
        }

        broadcast(new MessageSent($message));

        return ['ok' => true, 'message' => $message];
    }

    public function storedPayload(Message $message): array
    {
        return [
            'id' => $message->id,
            'time' => $message->created_at->format('H:i'),
            'type' => $message->type,
            'file' => $message->file_path
                ? ['url' => $message->fileUrl(), 'name' => $message->fileName(), 'width' => $message->width, 'height' => $message->height]
                : null,
        ];
    }

    public function conversationMeta(User $user, Collection $contacts): array
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
                'last' => $last ? $this->previewLabel($last) : '',
                'time' => $last ? $last->created_at->format('H:i') : '',
                'unread' => (int) ($unread[$contact->id] ?? 0),
                'sent' => $last ? $last->sender_id === $user->id : false,
                'read' => $last ? (bool) $last->read_at : false,
            ];
        }

        return $meta;
    }

    public function drafts(): array
    {
        return collect(session()->all())
            ->filter(fn ($value, $key) => str_starts_with($key, 'chat_draft:'))
            ->mapWithKeys(fn ($value, $key) => [str_replace('chat_draft:', '', $key) => $value])
            ->all();
    }

    public function saveDraft(string $to, ?string $text): void
    {
        if ($text !== null && $text !== '') {
            session()->put('chat_draft:'.$to, $text);
        } else {
            session()->forget('chat_draft:'.$to);
        }
    }

    public function presenceStatuses(array $usernames): array
    {
        if (empty($usernames)) {
            return [];
        }

        $ids = User::whereIn('username', $usernames)->pluck('id', 'username');

        $statuses = [];
        foreach ($ids as $username => $id) {
            $status = Cache::get("presence.status.{$id}");
            if ($status) {
                $statuses[$username] = $status;
            }
        }

        return $statuses;
    }

    public function setPresence(User $user, string $status): void
    {
        Cache::put("presence.status.{$user->id}", $status, now()->addMinutes(10));
        broadcast(new UserStatusChanged($user->username, $status));
    }

    public function broadcastTyping(User $sender, string $to, bool $typing): bool
    {
        $receiver = User::where('username', $to)->first();

        if (! $receiver) {
            return false;
        }

        broadcast(new TypingEvent(
            $receiver->id,
            $sender->username,
            $sender->name,
            $typing,
        ));

        return true;
    }

    public function addContact(User $user, string $username): array
    {
        $target = $username !== '' ? User::where('username', $username)->first() : null;

        if (! $target) {
            return ['ok' => false, 'error' => 'User not found.'];
        }

        if ($target->is($user)) {
            return ['ok' => false, 'error' => 'You cannot add yourself.'];
        }

        if ($user->contacts()->where('contact_user_id', $target->id)->exists()) {
            return ['ok' => false, 'error' => 'This user is already in your contacts.'];
        }

        try {
            $user->contacts()->attach($target->id);
        } catch (QueryException) {
            return ['ok' => false, 'error' => 'This user is already in your contacts.'];
        }

        return ['ok' => true, 'target' => $target];
    }

    public function updateContactNames(User $user, int $id, string $first, string $last): array
    {
        $contact = $user->contacts()
            ->where('contact_user_id', $id)
            ->first();

        if (! $contact) {
            return ['ok' => false];
        }

        $contact->pivot->update([
            'first_name' => $first !== '' ? $first : null,
            'last_name' => $last !== '' ? $last : null,
        ]);

        return ['ok' => true, 'contact' => $contact];
    }

    private function canOpenThread(User $viewer, User $other): bool
    {
        $isContact = $viewer->contacts()->where('contact_user_id', $other->id)->exists();

        $hasThread = Message::where(function ($q) use ($viewer, $other) {
            $q->where('sender_id', $viewer->id)->where('receiver_id', $other->id)
                ->orWhere(function ($q2) use ($viewer, $other) {
                    $q2->where('sender_id', $other->id)->where('receiver_id', $viewer->id);
                });
        })->exists();

        return $isContact || $hasThread;
    }

    private function messagePayload(Message $m, User $viewer): array
    {
        $data = [
            'id' => $m->id,
            'body' => $m->body,
            'time' => $m->created_at->format('H:i'),
            'from' => $m->sender_id === $viewer->id ? 'me' : 'other',
            'type' => $m->type,
            'read_at' => $m->read_at,
            'file' => $m->file_path
                ? ['url' => $m->fileUrl(), 'name' => $m->fileName(), 'width' => $m->width, 'height' => $m->height]
                : null,
        ];

        if ($data['from'] === 'other') {
            $data += $m->senderDisplayFor($viewer);
        }

        return $data;
    }

    private function previewLabel(Message $m): string
    {
        return match ($m->type) {
            'image' => 'Photo',
            'video' => 'Video',
            'document' => 'Document',
            default => $m->body,
        };
    }

    private function optimizeImage(string $path, string $mime): ?array
    {
        $size = @getimagesize($path);
        if (is_array($size)) {
            [$w, $h] = $size;
            $max = 1280;
            if ($w > $max || $h > $max) {
                $src = match ($mime) {
                    'image/jpeg' => @imagecreatefromjpeg($path),
                    'image/png' => @imagecreatefrompng($path),
                    'image/webp' => @imagecreatefromwebp($path),
                    default => null,
                };
                if ($src) {
                    $scale = $max / max($w, $h);
                    $nw = (int) round($w * $scale);
                    $nh = (int) round($h * $scale);
                    $dst = imagecreatetruecolor($nw, $nh);
                    if ($mime === 'image/png' || $mime === 'image/webp') {
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);
                    }
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                    if ($mime === 'image/jpeg') {
                        imageinterlace($dst, 1);
                    }
                    $tmp = $path.'.opt';
                    $ok = match ($mime) {
                        'image/jpeg' => imagejpeg($dst, $tmp, 80),
                        'image/png' => imagepng($dst, $tmp, 8),
                        'image/webp' => imagewebp($dst, $tmp, 80),
                        default => false,
                    };
                    imagedestroy($src);
                    imagedestroy($dst);
                    if ($ok) {
                        @rename($tmp, $path);
                        $w = $nw;
                        $h = $nh;
                    }
                }
            }

            return ['width' => $w, 'height' => $h];
        }

        if ($mime === 'image/svg+xml') {
            return $this->svgDimensions($path);
        }

        return null;
    }

    private function svgDimensions(string $path): ?array
    {
        $svg = @file_get_contents($path);
        if ($svg === false) {
            return null;
        }

        preg_match('/<svg[^>]*?width\s*=\s*["\']([\d.]+)["\']/', $svg, $w);
        preg_match('/<svg[^>]*?height\s*=\s*["\']([\d.]+)["\']/', $svg, $h);

        if (isset($w[1], $h[1])) {
            return ['width' => (int) $w[1], 'height' => (int) $h[1]];
        }

        if (preg_match('/viewBox\s*=\s*["\']\s*[\d.]+\s+[\d.]+\s+([\d.]+)\s+([\d.]+)\s*["\']/', $svg, $vb)) {
            return ['width' => (int) $vb[1], 'height' => (int) $vb[2]];
        }

        return null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'body',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function senderDisplayFor(User $viewer): array
    {
        $contact = $viewer->contacts()->where('contact_user_id', $this->sender_id)->first();
        $customName = ($contact?->pivot->first_name || $contact?->pivot->last_name)
            ? trim(($contact->pivot->first_name ?? '').' '.($contact->pivot->last_name ?? ''))
            : null;

        return [
            'sender' => $customName ?: $this->sender->name,
            'custom' => $customName !== null,
        ];
    }
}

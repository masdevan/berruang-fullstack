<?php

namespace App\Http\Controllers\Chat;

use App\Events\TypingEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'to' => ['required', 'string'],
            'typing' => ['required', 'boolean'],
        ]);

        $receiver = User::where('username', $request->to)->first();

        if (! $receiver) {
            return response()->json(['message' => 'User not found.'], 422);
        }

        $user = $request->user();

        broadcast(new TypingEvent(
            $receiver->id,
            $user->username,
            $user->name,
            (bool) $request->typing,
        ));

        return response()->json(['ok' => true]);
    }
}

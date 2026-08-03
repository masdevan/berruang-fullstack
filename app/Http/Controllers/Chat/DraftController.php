<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\Request;

class DraftController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function store(Request $request)
    {
        $to = $request->query('to');

        if (! $to) {
            return response()->noContent();
        }

        $this->chat->saveDraft($to, $request->query('text'));

        return response()->noContent();
    }
}

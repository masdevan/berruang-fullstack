<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DraftController extends Controller
{
    public function store(Request $request)
    {
        $to = $request->query('to');
        if (!$to) {
            return response()->noContent();
        }

        $text = $request->query('text');
        if ($text !== null && $text !== '') {
            session()->put('chat_draft:' . $to, $text);
        } else {
            session()->forget('chat_draft:' . $to);
        }

        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\Chat;

use App\Events\UserStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StatusController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $usernames = collect(explode(',', (string) $request->query('users')))->filter();

        if ($usernames->isEmpty()) {
            return response()->json([]);
        }

        $ids = User::whereIn('username', $usernames)->pluck('id', 'username');

        $statuses = [];
        foreach ($ids as $username => $id) {
            $status = Cache::get("presence.status.{$id}");
            if ($status) {
                $statuses[$username] = $status;
            }
        }

        return response()->json($statuses);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:online,idle']);

        $user = $request->user();

        Cache::put("presence.status.{$user->id}", $data['status'], now()->addMinutes(2));

        broadcast(new UserStatusChanged($user->username, $data['status']));

        return response()->json(['ok' => true]);
    }
}

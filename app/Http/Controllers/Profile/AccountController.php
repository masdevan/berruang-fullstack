<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Services\ProfileService;
use App\Services\WorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct(private readonly ProfileService $profile) {}

    public function index()
    {
        return view('profile.index', [
            'users' => auth()->user()->contacts()->orderBy('first_name')->limit(20)->get(),
            'workspaces' => app(WorkspaceService::class)->list(auth()->user()),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:500', 'regex:/^[^\p{Cc}\p{Zl}\p{Zp}]*$/u'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $error = $this->profile->updateAccount($user, $request->only(['name', 'username', 'bio']), $request->file('avatar'));

        if ($error) {
            return back()->withErrors(['username' => $error])->withInput();
        }

        return back()->with('account_status', 'Account details have been updated.');
    }
}

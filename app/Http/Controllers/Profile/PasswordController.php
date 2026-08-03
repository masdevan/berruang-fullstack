<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    private const FLASHED_FIELDS = ['password', 'password_confirmation'];

    public function __construct(private readonly ProfileService $profile) {}

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput(Arr::except($request->input(), self::FLASHED_FIELDS));
        }

        $error = $this->profile->updatePassword(
            $request->user(),
            $request->current_password,
            $request->password,
        );

        if ($error) {
            return back()
                ->withErrors(['current_password' => $error])
                ->withInput(Arr::except($request->input(), self::FLASHED_FIELDS));
        }

        return back()->with('status', 'Password has been updated.');
    }
}

<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    private const FLASHED_FIELDS = ['password', 'password_confirmation'];

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

        if (! Hash::check($request->current_password, $request->user()->password)) {
            return back()
                ->withErrors(['current_password' => 'Current password does not match our records.'])
                ->withInput(Arr::except($request->input(), self::FLASHED_FIELDS));
        }

        $request->user()->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return back()->with('status', 'Password has been updated.');
    }
}

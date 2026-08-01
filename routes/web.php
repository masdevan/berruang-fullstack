<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SetupProfileController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Chat\ContactController;
use App\Http\Controllers\Profile\AccountController;
use App\Http\Controllers\Profile\AvatarController;
use App\Http\Controllers\Profile\PasswordController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('check-username/{username}', function ($username) {
    return response()->json(['taken' => User::where('username', $username)->exists()]);
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/messages');
    }

    return view('auth.login');
});

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::get('/messages', function () {
        return view('chat.index', [
            'users' => auth()->user()->contacts()->orderBy('first_name')->limit(20)->get(),
            'onlineIds' => DB::table('sessions')->whereNotNull('user_id')->pluck('user_id')->all(),
        ]);
    })->name('chat');

    Route::get('/profile', function () {
        return view('profile.index', [
            'users' => auth()->user()->contacts()->orderBy('first_name')->limit(20)->get(),
            'onlineIds' => DB::table('sessions')->whereNotNull('user_id')->pluck('user_id')->all(),
        ]);
    })->name('profile');

    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::patch('/contacts/{id}', [ContactController::class, 'updateNames'])->name('contacts.update-names');

    Route::post('/profile/password', [PasswordController::class, 'update'])->name('profile.password');
    Route::post('/profile/avatar', [AvatarController::class, 'update'])->name('profile.avatar');
    Route::post('/profile/account', [AccountController::class, 'update'])->name('profile.account');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/setup-profile', [SetupProfileController::class, 'create'])->name('setup-profile');
    Route::post('/setup-profile', [SetupProfileController::class, 'store'])->name('setup-profile.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [VerifyEmailController::class, 'create'])->name('verification.notice');
    Route::post('verify-email', [VerifyEmailController::class, 'verify'])->name('verification.verify');
    Route::post('verify-email/resend', [VerifyEmailController::class, 'resend'])->name('verification.resend');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('auth/google', function () {
        return redirect('/login');
    })->name('auth.google');
});

Route::post('forgot-password', [ForgotPasswordController::class, 'sendCode'])->name('password.email');

Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');

Route::get('reset-password', [ResetPasswordController::class, 'create'])->name('password.reset');
Route::post('reset-password/verify', [ResetPasswordController::class, 'verifyCode'])->name('password.verify-code');
Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.store');

Route::post('logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Models\User;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/messages', function () {
        return view('chat.index');
    })->name('chat');
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

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendCode'])->name('password.email');

    Route::get('reset-password', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password/verify', [ResetPasswordController::class, 'verifyCode'])->name('password.verify-code');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.store');

    Route::get('auth/google', function () {
        return redirect('/login');
    })->name('auth.google');
});

Route::post('logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

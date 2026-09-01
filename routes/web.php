<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SetupProfileController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ContactController;
use App\Http\Controllers\Chat\DraftController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Chat\StatusController;
use App\Http\Controllers\Chat\TypingController;
use App\Http\Controllers\Chat\WorkspaceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Profile\AccountController;
use App\Http\Controllers\Profile\PasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::view('/landing', 'landing')->name('landing');

Route::get('check-username/{username}', [ContactController::class, 'checkUsername']);

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::get('/messages', [ChatController::class, 'index'])->name('chat');

    Route::get('/profile', [AccountController::class, 'index'])->name('profile');

    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/options', [ContactController::class, 'options'])->name('contacts.options');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::patch('/contacts/{id}', [ContactController::class, 'updateNames'])->name('contacts.update-names');
    Route::get('/messages/thread', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::post('/messages/read', [MessageController::class, 'markRead'])->name('messages.read');

    Route::get('/presence-status', [StatusController::class, 'index'])->name('presence-status.index');
    Route::post('/presence-status', [StatusController::class, 'store'])->name('presence-status.store');

    Route::post('/typing', [TypingController::class, 'store'])->name('typing.store');
    Route::post('/chat/draft', [DraftController::class, 'store'])->name('chat.draft.store');

    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::post('/workspaces/join', [WorkspaceController::class, 'join'])->name('workspaces.join');
    Route::get('/workspaces/{code}/members', [WorkspaceController::class, 'members'])->name('workspaces.members');
    Route::post('/workspaces/{code}/members', [WorkspaceController::class, 'inviteMember'])->name('workspaces.members.add');
    Route::post('/workspaces/{code}/members/kick', [WorkspaceController::class, 'kickMembers'])->name('workspaces.members.kick');
    Route::post('/workspaces/{code}/members/{id}/promote', [WorkspaceController::class, 'promoteMember'])->name('workspaces.members.promote');
    Route::post('/workspaces/{code}/members/{id}/demote', [WorkspaceController::class, 'demoteMember'])->name('workspaces.members.demote');
    Route::get('/workspaces/{code}/messages', [WorkspaceController::class, 'messages'])->name('workspaces.messages');
    Route::post('/workspaces/{code}/messages', [WorkspaceController::class, 'sendMessage'])->name('workspaces.messages.send');
    Route::post('/workspaces/{code}/read', [WorkspaceController::class, 'markRead'])->name('workspaces.read');
    Route::post('/workspaces/{code}/typing', [WorkspaceController::class, 'typing'])->name('workspaces.typing');
    Route::post('/workspaces/{code}/invite-response', [WorkspaceController::class, 'respondInvite'])->name('workspaces.invite-response');
    Route::post('/workspaces/{code}/leave', [WorkspaceController::class, 'leave'])->name('workspaces.leave');
    Route::post('/workspaces/{code}/configure', [WorkspaceController::class, 'configure'])->name('workspaces.configure');

    Route::post('/profile/password', [PasswordController::class, 'update'])->name('profile.password');
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

    Route::redirect('auth/google', '/login')->name('auth.google');
});

Route::post('forgot-password', [ForgotPasswordController::class, 'sendCode'])->name('password.email');

Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');

Route::get('reset-password', [ResetPasswordController::class, 'create'])->name('password.reset');
Route::post('reset-password/verify', [ResetPasswordController::class, 'verifyCode'])->name('password.verify-code');
Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.store');

Route::post('logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('account details can be updated and the username change starts the 7 day lock', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/profile/account', [
        'name' => 'New Name',
        'username' => 'new_username',
        'bio' => 'A short bio about me.',
    ])->assertRedirect();

    $user->refresh();

    expect($user->name)->toBe('New Name');
    expect($user->username)->toBe('new_username');
    expect($user->bio)->toBe('A short bio about me.');
    expect($user->username_changed_at)->not->toBeNull();
});

test('username cannot be changed twice within 7 days', function () {
    $user = User::factory()->create(['username_changed_at' => now()->subDay()])->fresh();

    $this->actingAs($user)->post('/profile/account', [
        'name' => $user->name,
        'username' => 'another_username',
    ])->assertSessionHasErrors('username');

    expect($user->refresh()->username)->not->toBe('another_username');
});

test('username can be changed again after 7 days', function () {
    $user = User::factory()->create(['username_changed_at' => now()->subDays(8)]);

    $this->actingAs($user)->post('/profile/account', [
        'name' => $user->name,
        'username' => 'fresh_username',
    ])->assertRedirect();

    expect($user->refresh()->username)->toBe('fresh_username');
});

test('password can be changed with the current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/profile/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect()->assertSessionHas('status');

    expect(Hash::check('new-password-123', $user->refresh()->password))->toBeTrue();
});

test('current password is preserved when the new password fails validation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/profile/password', [
        'current_password' => 'password',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');

    expect(session()->getOldInput('current_password'))->toBe('password');
    expect(session()->getOldInput('password'))->toBeNull();
    expect(session()->getOldInput('password_confirmation'))->toBeNull();
});

test('current password is preserved when it does not match', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/profile/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertSessionHasErrors('current_password');

    expect(session()->getOldInput('current_password'))->toBe('wrong-password');
});

test('bio rejects control characters', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/profile/account', [
        'name' => $user->name,
        'username' => $user->username,
        'bio' => "line one\nline two",
    ])->assertSessionHasErrors('bio');
});

test('avatar can be uploaded with the account details', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->post('/profile/account', [
        'name' => $user->name,
        'username' => $user->username,
        'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
    ])->assertRedirect()->assertSessionHas('account_status');

    expect($user->refresh()->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);
    expect($user->avatar_preview_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_preview_path);
    expect(Storage::disk('public')->size($user->avatar_preview_path))->toBeLessThanOrEqual(10 * 1024);
});

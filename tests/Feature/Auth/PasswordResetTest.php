<?php

use App\Mail\PasswordResetCodeMail;
use App\Models\EmailCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

test('the reset page requires starting from the forgot password step', function () {
    $this->get('/reset-password')
        ->assertRedirect(route('password.request'));
});

test('forgot password sends a reset code for an existing user', function () {
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertRedirect(route('password.reset'));

    $code = EmailCode::where('email', $user->email)
        ->where('purpose', 'reset_password')
        ->first();

    expect($code)->not->toBeNull()
        ->and(session('reset_email'))->toBe($user->email);

    Mail::assertSent(PasswordResetCodeMail::class, fn ($mail) => $mail->hasTo($user->email) && $mail->code === $code->code);
});

test('forgot password does not leak whether an email is registered', function () {
    $this->post('/forgot-password', ['email' => 'unknown@example.com'])
        ->assertRedirect(route('password.reset'));

    Mail::assertNothingSent();
});

test('the code step comes before the password step', function () {
    $user = User::factory()->create();
    $this->post('/forgot-password', ['email' => $user->email]);

    $this->get('/reset-password')
        ->assertOk()
        ->assertSee('name="code[]"', false)
        ->assertSee('name="email"', false)
        ->assertDontSee('name="password"', false);
});

test('a valid code unlocks the password step', function () {
    $user = User::factory()->create();
    $this->post('/forgot-password', ['email' => $user->email]);

    $code = EmailCode::where('email', $user->email)
        ->where('purpose', 'reset_password')
        ->first();

    $this->post('/reset-password/verify', [
        'email' => $user->email,
        'code' => str_split($code->code),
    ])->assertRedirect(route('password.reset'));

    expect(session('reset_code_verified'))->toBeTrue();

    $this->get('/reset-password')
        ->assertOk()
        ->assertSee('name="password"', false)
        ->assertDontSee('name="code[]"', false);
});

test('an invalid code is rejected without unlocking the password step', function () {
    $user = User::factory()->create();
    $this->post('/forgot-password', ['email' => $user->email]);

    $this->post('/reset-password/verify', [
        'email' => $user->email,
        'code' => ['1', '1', '1', '1', '1', '1'],
    ])->assertSessionHasErrors('code');

    expect(session('reset_code_verified'))->toBeNull();
});

test('verifying with an unknown email is rejected', function () {
    $this->post('/reset-password/verify', [
        'email' => 'unknown@example.com',
        'code' => ['1', '1', '1', '1', '1', '1'],
    ])->assertSessionHasErrors('code');
});

test('reset password is blocked before the code is verified', function () {
    $user = User::factory()->create();
    $this->post('/forgot-password', ['email' => $user->email]);

    $this->post('/reset-password', [
        'password' => 'passwordbaru123',
        'password_confirmation' => 'passwordbaru123',
    ])->assertRedirect(route('password.request'));

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('a verified flow resets the password and invalidates the remember token', function () {
    $user = User::factory()->create(['remember_token' => 'old-remember-token', 'onboarded_at' => now()]);
    $this->post('/forgot-password', ['email' => $user->email]);

    $code = EmailCode::where('email', $user->email)
        ->where('purpose', 'reset_password')
        ->first();

    $this->post('/reset-password/verify', [
        'email' => $user->email,
        'code' => str_split($code->code),
    ]);

    $this->post('/reset-password', [
        'password' => 'passwordbaru123',
        'password_confirmation' => 'passwordbaru123',
    ])->assertRedirect(route('login'));

    expect(Hash::check('passwordbaru123', $user->fresh()->password))->toBeTrue()
        ->and($user->fresh()->remember_token)->not->toBe('old-remember-token')
        ->and(session('reset_code_verified'))->toBeNull()
        ->and(session('reset_email'))->toBeNull();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'passwordbaru123',
    ])->assertRedirect('/messages');
});

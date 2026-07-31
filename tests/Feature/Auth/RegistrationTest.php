<?php

use App\Mail\VerificationCodeMail;
use App\Models\EmailCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

test('register creates an unverified user and sends a verification code', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'username' => 'budi_santoso',
        'email' => 'budi@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticated();

    $user = User::where('email', 'budi@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->email_verified_at)->toBeNull();

    $code = EmailCode::where('email', 'budi@example.com')
        ->where('purpose', 'verify_email')
        ->first();

    expect($code)->not->toBeNull();

    Mail::assertSent(VerificationCodeMail::class, fn ($mail) => $mail->hasTo('budi@example.com') && $mail->code === $code->code);
});

test('register rejects a duplicate email without sending a code', function () {
    User::factory()->create(['email' => 'budi@example.com']);

    $this->post('/register', [
        'name' => 'Budi Lain',
        'username' => 'budi_lain',
        'email' => 'budi@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHasErrors('email');

    Mail::assertNothingSent();
});

<?php

use App\Mail\VerificationCodeMail;
use App\Models\EmailCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function createVerifyCode(User $user): EmailCode
{
    return EmailCode::create([
        'email' => $user->email,
        'purpose' => 'verify_email',
        'code' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);
}

beforeEach(function () {
    Mail::fake();
});

test('unverified user is blocked from the chat page', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/messages')
        ->assertRedirect(route('verification.notice'));
});

test('verified user can open the chat page', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get('/messages')
        ->assertOk();
});

test('an unconfigured user is blocked from the chat page', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    $this->actingAs($user)
        ->get('/messages')
        ->assertRedirect('/setup-profile');

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect('/messages');
});

test('a valid code verifies the email address', function () {
    $user = User::factory()->unverified()->create(['onboarded_at' => null]);
    $code = createVerifyCode($user);

    $this->actingAs($user)
        ->post('/verify-email', ['code' => str_split($code->code)])
        ->assertRedirect('/setup-profile');

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('a verified user is sent to the chat page', function () {
    $user = User::factory()->unverified()->create(['onboarded_at' => now()]);
    $code = createVerifyCode($user);

    $this->actingAs($user)
        ->post('/verify-email', ['code' => str_split($code->code)])
        ->assertRedirect('/messages');
});

test('a consumed code cannot be used twice', function () {
    $user = User::factory()->unverified()->create();
    $code = createVerifyCode($user);

    $this->actingAs($user)->post('/verify-email', ['code' => str_split($code->code)]);
    $this->actingAs($user)->post('/verify-email', ['code' => str_split($code->code)])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('an invalid code is rejected', function () {
    $user = User::factory()->unverified()->create();
    createVerifyCode($user);

    $this->actingAs($user)
        ->post('/verify-email', ['code' => ['9', '9', '9', '9', '9', '9']])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->email_verified_at)->toBeNull();
});

test('resend sends a new code but is throttled for one minute', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->post('/verify-email/resend')
        ->assertSessionHas('status', 'A new code has been sent to your email.');

    $this->actingAs($user)->post('/verify-email/resend')
        ->assertSessionHas('status', 'Please wait a minute before requesting a new code.');

    Mail::assertSent(VerificationCodeMail::class, 1);
});

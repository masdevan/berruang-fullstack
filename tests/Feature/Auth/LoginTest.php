<?php

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('a verified user can sign in', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/messages');

    $this->assertAuthenticatedAs($user);
});

test('an unconfigured user is sent to the setup profile page after sign in', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/setup-profile');

    $this->assertAuthenticatedAs($user);
});

test('sign in fails with wrong credentials', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password-salah',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('an unverified user is sent to the verification page and gets a fresh code after sign in', function () {
    Mail::fake();

    $user = User::factory()->unverified()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('verification.notice'));

    $this->assertAuthenticatedAs($user);

    Mail::assertSent(VerificationCodeMail::class, fn ($mail) => $mail->hasTo($user->email));
});

test('remember me issues a recaller cookie and persists the token', function () {
    $user = User::factory()->create(['remember_token' => null, 'onboarded_at' => now()]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'remember' => '1',
    ]);

    $response->assertRedirect('/messages');

    $recallerCookie = collect($response->headers->getCookies())
        ->first(fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_'));

    expect($recallerCookie)->not->toBeNull()
        ->and($user->fresh()->remember_token)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

test('remember me unchecked does not issue a recaller cookie', function () {
    $user = User::factory()->create(['remember_token' => null, 'onboarded_at' => now()]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/messages');

    expect($user->fresh()->remember_token)->toBeNull();
});

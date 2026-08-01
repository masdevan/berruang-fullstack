<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('reset password status message renders only once', function () {
    Mail::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    $response = $this->get(route('password.reset'));

    $response->assertOk();
    $count = substr_count($response->getContent(), 'A reset code has been sent to your email.');
    expect($count)->toBe(1);
});

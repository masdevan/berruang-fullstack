<?php

use App\Mail\PasswordResetCodeMail;
use App\Mail\VerificationCodeMail;

test('the verification email renders with the code and brand', function () {
    $html = (new VerificationCodeMail('Budi Santoso', '123456'))->render();

    expect($html)->toContain('123456')
        ->toContain('Budi Santoso')
        ->toContain('Ber<span style="color:#E091A9;">Ruang</span>');
});

test('the password reset email renders with the code and brand', function () {
    $html = (new PasswordResetCodeMail('Budi Santoso', '654321'))->render();

    expect($html)->toContain('654321')
        ->toContain('Budi Santoso')
        ->toContain('Ber<span style="color:#E091A9;">Ruang</span>');
});

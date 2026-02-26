<?php

use App\Mail\SignupPinMail;
use App\Models\SignupVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

test('user can register through email pin and password', function () {
    Mail::fake();

    $email = 'candidate@gmail.com';

    $this->post('/register/email', ['email' => $email])
        ->assertRedirect(route('register.pin'));

    Mail::assertSent(SignupPinMail::class);
    $this->assertDatabaseHas('signup_verifications', ['email' => $email]);

    SignupVerification::query()
        ->where('email', $email)
        ->latest('id')
        ->firstOrFail()
        ->update(['pin_hash' => Hash::make('1234')]);

    $this->postJson('/register/pin/verify', ['pin' => '1234'])
        ->assertOk()
        ->assertJson(['ok' => true]);

    $this->get('/register/password')->assertOk();

    $this->post('/register/password', [
        'password' => 'very-secure-password',
    ])->assertRedirect(route('lk.profile'));

    $this->assertAuthenticated();
    expect(User::query()->where('email', $email)->exists())->toBeTrue();
});

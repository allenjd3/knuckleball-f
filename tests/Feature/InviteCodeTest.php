<?php

use App\Models\User;
use RyanChandler\LaravelCloudflareTurnstile\Facades\Turnstile;

test('a user can register with a valid turnstile response', function () {
    $fake = Turnstile::fake();

    $this->post(route('register'), [
        'name' => 'Joe Schmo',
        'email' => 'joe@jamesdallen.me',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'cf-turnstile-response' => $fake->dummy(),
    ])->assertValid();
});

test('a user cannot register without a turnstile response', function () {
    Turnstile::fake();

    $this->post(route('register'), [
        'name' => 'Joe Schmo',
        'email' => 'joe@jamesdallen.me',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertInvalid('cf-turnstile-response');
});

test('a user cannot register with a failed turnstile response', function () {
    Turnstile::fake()->fail();

    $this->post(route('register'), [
        'name' => 'Joe Schmo',
        'email' => 'joe@jamesdallen.me',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'cf-turnstile-response' => 'invalid-token',
    ])->assertInvalid('cf-turnstile-response');
});

test('a new user is published as soon as they register', function () {
    $fake = Turnstile::fake();

    $this->post(route('register'), [
        'name' => 'Joe Schmo',
        'email' => 'joe@jamesdallen.me',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'cf-turnstile-response' => $fake->dummy(),
    ])->assertValid();

    expect(User::firstWhere('name', 'Joe Schmo')->isPublished())->toBeTrue();
});

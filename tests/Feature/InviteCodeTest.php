<?php

use App\Models\User;
use App\Models\InviteCode;

test('valid invite codes are necessary to register a new user', function () {
    $userData = User::factory()->make()->only(['name', 'email', 'password']);
    $userData = [
        'name' => 'Joe Schmo',
        'email' => 'joe@jamesdallen.me',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'code' => 'INVITE',
    ];

    $this->post(route('register'), $userData)->assertInvalid('code');
});

test('a user can be registers with a valid code', function () {
    $inviteCode = InviteCode::factory()->create();
    $userData = User::factory()->make()->only(['name', 'email', 'password']);
    $userData = [
        'name' => 'Joe Schmo',
        'email' => 'joe@jamesdallen.me',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'code' => $inviteCode->code,
    ];

    $this->post(route('register'), $userData)->assertValid();
});

test('a user cannot register with a code that has no remaining uses', function() {
    $inviteCode = InviteCode::factory()->state(['remaining' => 0])->create();
    $userData = User::factory()->make()->only(['name', 'email', 'password']);
    $userData = [
        'name' => 'Joe Schmo',
        'email' => 'joe@jamesdallen.me',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'code' => $inviteCode->code,
    ];

    $this->post(route('register'), $userData)->assertInvalid('code');
});

test('invite codes decrement after use', function() {
    $inviteCode = InviteCode::factory()->state(['remaining' => 2])->create();
    $userData = User::factory()->make()->only(['name', 'email', 'password']);
    $userData = [
        'name' => 'Joe Schmo',
        'email' => 'joe@jamesdallen.me',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'code' => $inviteCode->code,
    ];

    $this->post(route('register'), $userData)->assertValid();
    $this->assertEquals(1, $inviteCode->fresh()->remaining);
});

<?php

use App\Models\User;

test('They can see a user\'s profile', function () {
    $user = User::factory()->create();
    $this->get(route('users.profile', $user))
        ->assertOk();
});

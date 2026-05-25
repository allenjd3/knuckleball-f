<?php

use App\Models\User;

it('shows log in and join free links for guests', function () {
    $this->get(route('home'))
        ->assertStatus(200)
        ->assertSee('Log In')
        ->assertSee('Join Free')
        ->assertDontSee('TTM Database');
});

it('shows ttm database link instead of login buttons when authenticated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertStatus(200)
        ->assertSee('TTM Database')
        ->assertDontSee('Join Free')
        ->assertDontSee('Log In');
});

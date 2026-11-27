<?php

use App\Actions\CreateFeedItem;
use App\Models\PostalMail;
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

it('does not show a failed return as a mail day in the ticker', function () {
    $postalMail = PostalMail::factory()->failed()->create(['returned_date' => now()->subDay()]);
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('Mail day for @' . basename($postalMail->user->path()))
        ->assertSee('@' . basename($postalMail->user->path()) . ' sent:', false);
});

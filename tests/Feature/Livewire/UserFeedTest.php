<?php

use App\Livewire\UserFeed;
use App\Models\Player;
use App\Models\PostalMail;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(UserFeed::class)
        ->assertStatus(200);
});

test('it shows trending signers', function () {

    Player::factory(10)->has(PostalMail::factory())->create();
    $player = Player::factory()
        ->has(PostalMail::factory(3)->returned())
        ->create();

    Livewire::test(UserFeed::class)
        ->assertSee($player->name);
});

<?php

use App\Livewire\EventsLanding;
use App\Models\Event;
use App\Models\Player;
use App\Models\User;
use Livewire\Livewire;

it('hides a signing player\'s photo from guests but shows it to authenticated users', function () {
    $player = Player::factory()->create();
    $player->media()->create(['url' => 'avatars/test.jpg']);
    $event = Event::factory()->approved()->playerSigning($player)->create();

    Livewire::test(EventsLanding::class)
        ->assertDontSee('avatars/test.jpg');

    Livewire::actingAs(User::factory()->create())
        ->test(EventsLanding::class)
        ->assertSee('avatars/test.jpg');
});

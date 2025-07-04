<?php

use App\Livewire\ShowPlayer;
use App\Models\Player;
use App\Models\User;

test('it can create addresses', function () {
    $user = User::factory()->isSuperAdmin()->create();
    $player = Player::factory()->create();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction('createAddress', data: [
            'address_1' => '2671 Rochester Ave',
            'city' => 'Hamilton',
            'state' => 'Ohio',
            'postal_code' => '45011',
        ])
        ->assertHasNoActionErrors();
});

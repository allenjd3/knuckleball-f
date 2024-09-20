<?php


test('Unauthenticated users can view players', function () {
    $this->get('players')->assertOk();
});

test('Players are visible in the livewire component', function() {

    $player = \App\Models\Player::factory()->create();

    $viewPlayers = Livewire::test('ViewPlayers')
        ->assertCountTableRecords(1);
});

<?php

use App\Models\Team;

test('Unauthenticated users can view teams', function () {
    $this->get('teams')->assertOk();
});

test('Teams are visible in the livewire component', function () {

    $team = Team::factory()->create();
    Livewire::test('ViewTeams')
        ->assertSee($team->name);
});

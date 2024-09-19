<?php


test('Unauthenticated users can view teams', function () {
    $this->get('teams')->assertOk();
});

test('Teams are visible in the livewire component', function() {

    $team = \App\Models\Team::factory()->create();
    Livewire::test('ViewTeams')
        ->assertSee($team->name);
});

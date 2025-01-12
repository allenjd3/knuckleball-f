<?php

use App\Livewire\ViewTeams;
use App\Models\Team;

test('Unauthenticated users can view teams', function () {
    $this->get('teams')->assertOk();
});

test('Teams are visible in the livewire component', function () {
    $team = Team::factory()->create();

    Livewire::test('ViewTeams')
        ->assertSee($team->name);
});

test('Teams can be sorted', function () {
    $teams = Team::factory(10)->state(['published_at' => now()->subWeek()])->create();

    Livewire::test(ViewTeams::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($teams->sortBy('name'), inOrder: true);
});

<?php

use App\Livewire\ViewTeams;
use App\Models\Team;
use App\Models\User;
use App\Notifications\PendingApprovals;

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

test('A user can create a team', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(ViewTeams::class)
        ->callAction('createTeam', ['name' => 'Some Name'])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('teams', [
        'name' => 'Some Name',
        'published_at' => null,
    ]);
});

test('Non users cannot create teams', function () {
    Livewire::test(ViewTeams::class)
        ->assertActionHidden('createTeam');
});

test('a team created by a user is not visible until approved by an admin', function () {
    Team::factory(3)->create();
    $team = Team::factory()->state(['published_at' => null])->create();

    Livewire::test(ViewTeams::class)
        ->assertCanNotSeeTableRecords(Team::find([$team->id]));
});

test('approved teams are still visible', function () {
    Team::factory(3)->create();
    $team = Team::factory()->state(['published_at' => now()->subDay()])->create();

    Livewire::test(ViewTeams::class)
        ->assertCanSeeTableRecords(Team::find([$team->id]));
});

test('an admin is emailed if new teams are added', function () {
    Notification::fake([PendingApprovals::class]);
    $this->artisan('notify:pending-approvals');

    Notification::assertSentTimes(PendingApprovals::class, 1);
});


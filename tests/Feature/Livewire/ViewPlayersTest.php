<?php

use App\Enums\Role;
use App\Livewire\ViewPlayers;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;

test('Unauthenticated users can view players', function () {
    $this->get('players')->assertOk();
});

test('Players are visible in the livewire component', function () {

    Player::factory()->published()->create();

    Livewire::test('ViewPlayers')
        ->assertCountTableRecords(1);
});

test('Unpublished players don\'t show up', function () {
    $published = Player::factory(1)->published()->create();
    $unpublished = Player::factory(1)->unPublished()->create();

    Player::factory()->published()->create();

    Livewire::test('ViewPlayers')
        ->assertCanSeeTableRecords($published)
        ->assertCanNotSeeTableRecords($unpublished);
});

test('Players can be sorted by name', function () {
    $players = Player::factory(10)->published()->create();

    Livewire::test('ViewPlayers')
        ->sortTable('name')
        ->assertCanSeeTableRecords($players->sortBy('name'), inOrder: true);
});

test('Players can be updated by super admins', function () {
    $team = Team::factory()->create();
    $team2 = Team::factory()->create();
    $user = User::factory()->isSuperAdmin()->create();

    $oldData = [
        'name' => 'Joe DeScoobio',
        'team_id' => $team->id,
        'last_team_id' => null,
        'published_at' => now()->subWeek(),
        'retired_at' => null,
    ];

    $player = Player::factory()->create($oldData);

    $updatedData = [
        'name' => 'Joe DePoopio',
        'team_id' => $team2->id,
        'last_team_id' => $team->id,
    ];

    Livewire::actingAs($user)
        ->test(ViewPlayers::class)
        ->callAction(TestAction::make('edit')->table($player), $updatedData)
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('players', $updatedData);
});

test('Players cannot be updated by non-users', function () {
    $player = Player::factory()->published()->create();
    Livewire::test('ViewPlayers')->assertActionHidden(TestAction::make('edit')->table($player));
});

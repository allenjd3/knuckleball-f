<?php

use App\Filament\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;

beforeEach(function () {
    // Create an admin user - adjust this based on your auth setup
    $this->admin = User::factory()->isSuperAdmin()->create();
    $this->actingAs($this->admin);
});

it('can render the list page', function () {
    get(PlayerResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list players', function () {
    $players = Player::factory()->count(3)->create();

    Livewire::test(PlayerResource\Pages\ListPlayers::class)
        ->assertCanSeeTableRecords($players);
});

it('can render the create page', function () {
    get(PlayerResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create a player', function () {
    $team = Team::factory()->create();
    $newData = [
        'name' => 'Test Player',
        'team_id' => $team->id,
        'note' => 'Test note',
        'dmca_certification' => true,
    ];

    Livewire::test(PlayerResource\Pages\CreatePlayer::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('players', [
        'name' => 'Test Player',
        'team_id' => $team->id,
        'note' => 'Test note',
    ]);
});

it('validates required fields on create', function () {
    Livewire::test(PlayerResource\Pages\CreatePlayer::class)
        ->fillForm([
            'name' => null,
            'team_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'team_id' => 'required']);
});

it('can render the edit page', function () {
    $player = Player::factory()->create();

    get(PlayerResource::getUrl('edit', ['record' => $player]))
        ->assertSuccessful();
});

it('can retrieve data for editing', function () {
    $player = Player::factory()->create();

    Livewire::test(PlayerResource\Pages\EditPlayer::class, [
        'record' => $player->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $player->name,
            'team_id' => $player->team_id,
            'note' => $player->note,
        ]);
});

it('can update a player', function () {
    $player = Player::factory()->create();
    $team = Team::factory()->create();

    $newData = [
        'name' => 'Updated Name',
        'team_id' => $team->id,
        'note' => 'Updated note',
        'dmca_certification' => true,
    ];

    Livewire::test(PlayerResource\Pages\EditPlayer::class, [
        'record' => $player->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas('players', [
        'id' => $player->id,
        'name' => 'Updated Name',
        'team_id' => $team->id,
        'note' => 'Updated note',
    ]);
});

it('can delete a player', function () {
    $player = Player::factory()->create();

    Livewire::test(PlayerResource\Pages\ListPlayers::class)
        ->callTableAction('delete', $player);

    assertModelMissing($player);
});

it('can search players by name', function () {
    $players = Player::factory()->count(3)->create();
    $searchPlayer = $players->first();

    Livewire::test(PlayerResource\Pages\ListPlayers::class)
        ->searchTable($searchPlayer->name)
        ->assertCanSeeTableRecords([$searchPlayer])
        ->assertCanNotSeeTableRecords($players->skip(1));
});

it('can sort players by name', function () {
    $players = Player::factory()->count(3)->create();

    Livewire::test(PlayerResource\Pages\ListPlayers::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($players->sortBy('name'), inOrder: true);
});

it('displays correct player status badge', function () {
    $unpublished = Player::factory()->create(['published_at' => null]);
    $active = Player::factory()->create([
        'published_at' => now(),
        'retired_at' => null,
    ]);
    $retired = Player::factory()->create([
        'published_at' => now(),
        'retired_at' => now()->subYear(),
    ]);

    Livewire::test(PlayerResource\Pages\ListPlayers::class)
        ->assertTableColumnStateSet('retired_at_status', 'Unpublished', $unpublished)
        ->assertTableColumnStateSet('retired_at_status', 'Active', $active)
        ->assertTableColumnStateSet('retired_at_status', 'Retired', $retired);
});

it('can filter by rejected status', function () {
    $rejectedPlayer = Player::factory()->create(['rejected' => true]);
    $acceptedPlayer = Player::factory()->create(['rejected' => false]);

    Livewire::test(PlayerResource\Pages\ListPlayers::class)
        ->filterTable('rejected', true)
        ->assertCanSeeTableRecords([$rejectedPlayer])
        ->assertCanNotSeeTableRecords([$acceptedPlayer]);
});

it('can bulk publish players', function () {
    $players = Player::factory()->count(3)->create(['published_at' => null]);

    Livewire::test(PlayerResource\Pages\ListPlayers::class)
        ->callTableBulkAction('publish', $players);

    foreach ($players as $player) {
        expect($player->fresh()->published_at)->not->toBeNull();
    }
});

it('can bulk delete players', function () {
    $players = Player::factory()->count(3)->create();

    Livewire::test(PlayerResource\Pages\ListPlayers::class)
        ->callTableBulkAction('delete', $players);

    foreach ($players as $player) {
        assertModelMissing($player);
    }
});

it('validates note max length', function () {
    $team = Team::factory()->create();

    Livewire::test(PlayerResource\Pages\CreatePlayer::class)
        ->fillForm([
            'name' => 'Test Player',
            'team_id' => $team->id,
            'note' => str_repeat('a', 501),
        ])
        ->call('create')
        ->assertHasFormErrors(['note' => 'max']);
});

it('can set dates for player', function () {
    $team = Team::factory()->create();
    $publishedAt = now();
    $retiredAt = now()->addYear();
    $deceasedAt = now()->addYears(2);

    Livewire::test(PlayerResource\Pages\CreatePlayer::class)
        ->fillForm([
            'name' => 'Test Player',
            'team_id' => $team->id,
            'published_at' => $publishedAt,
            'retired_at' => $retiredAt,
            'deceased_at' => $deceasedAt,
            'dmca_certification' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('players', [
        'name' => 'Test Player',
        'published_at' => $publishedAt->toDateString(),
        'retired_at' => $retiredAt->toDateString(),
        'deceased_at' => $deceasedAt->toDateString(),
    ]);
});

it('can associate player with user', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    Livewire::test(PlayerResource\Pages\CreatePlayer::class)
        ->fillForm([
            'name' => 'Test Player',
            'team_id' => $team->id,
            'user_id' => $user->id,
            'dmca_certification' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('players', [
        'name' => 'Test Player',
        'user_id' => $user->id,
    ]);
});

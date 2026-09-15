<?php

use App\Livewire\GlobalSearch;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

it('returns players matching a partial name', function () {
    Player::factory()->published()->create(['name' => 'Ken Griffey Jr.']);
    Player::factory()->published()->create(['name' => 'Babe Ruth']);

    $component = Livewire::test(GlobalSearch::class)
        ->set('query', 'griffey');

    expect($component->get('players')->pluck('name')->all())->toBe(['Ken Griffey Jr.']);
});

it('returns nothing for a query shorter than 2 characters', function () {
    Player::factory()->published()->create(['name' => 'Ken Griffey Jr.']);

    $component = Livewire::test(GlobalSearch::class)
        ->set('query', 'k');

    expect($component->get('players'))->toBeEmpty();
});

it('excludes unpublished and rejected players', function () {
    Player::factory()->unPublished()->create(['name' => 'Ken Griffey Unpublished']);
    Player::factory()->published()->create(['name' => 'Ken Griffey Rejected', 'rejected' => true]);
    $visible = Player::factory()->published()->create(['name' => 'Ken Griffey Jr.']);

    $component = Livewire::test(GlobalSearch::class)
        ->set('query', 'ken griffey');

    expect($component->get('players')->pluck('id')->all())->toBe([$visible->id]);
});

it('limits player results to 5', function () {
    Player::factory()
        ->count(10)
        ->published()
        ->sequence(fn ($sequence) => ['name' => 'Ken Griffey ' . $sequence->index])
        ->create();

    $component = Livewire::test(GlobalSearch::class)
        ->set('query', 'ken griffey');

    expect($component->get('players'))->toHaveCount(5);
});

it('returns teams matching a partial name', function () {
    Team::factory()->published()->create(['name' => 'Thunderbolts']);
    Team::factory()->published()->create(['name' => 'Warriors']);

    $component = Livewire::test(GlobalSearch::class)
        ->set('query', 'thunder');

    expect($component->get('teams')->pluck('name')->all())->toBe(['Thunderbolts']);
});

it('excludes unpublished and rejected teams', function () {
    Team::factory()->create(['name' => 'Thunder Unpublished', 'published_at' => null]);
    Team::factory()->published()->create(['name' => 'Thunder Rejected', 'rejected' => true]);
    $visible = Team::factory()->published()->create(['name' => 'Thunderbolts']);

    $component = Livewire::test(GlobalSearch::class)
        ->set('query', 'thunder');

    expect($component->get('teams')->pluck('id')->all())->toBe([$visible->id]);
});

it('shows a player photo to authenticated users but not guests', function () {
    $player = Player::factory()->published()->create(['name' => 'Ken Griffey Jr.']);
    $player->media()->create(['url' => 'avatars/test.jpg']);

    Livewire::test(GlobalSearch::class)
        ->set('query', 'griffey')
        ->assertDontSee('avatars/test.jpg');

    Livewire::actingAs(User::factory()->create())
        ->test(GlobalSearch::class)
        ->set('query', 'griffey')
        ->assertSee('avatars/test.jpg');
});

it('shows a team logo to everyone, including guests', function () {
    $team = Team::factory()->published()->create(['name' => 'Thunderbolts']);
    $team->media()->create(['url' => 'teams/test.jpg']);

    Livewire::test(GlobalSearch::class)
        ->set('query', 'thunder')
        ->assertSee('teams/test.jpg');
});

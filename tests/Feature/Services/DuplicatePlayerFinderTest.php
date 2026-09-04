<?php

use App\Models\Player;
use App\Models\Team;
use App\Services\DuplicatePlayerFinder;
use Illuminate\Support\Collection;

function findDuplicates(): Collection
{
    return app(DuplicatePlayerFinder::class)->find();
}

test('it groups players with the same name and team as duplicates', function () {
    $team = Team::factory()->create();
    $first = Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id]);
    $second = Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id]);

    $groups = findDuplicates();

    expect($groups)->toHaveCount(1)
        ->and($groups->first()->pluck('id')->sort()->values()->all())
        ->toBe(collect([$first->id, $second->id])->sort()->values()->all());
});

test('it matches names regardless of case and surrounding whitespace', function () {
    $team = Team::factory()->create();
    Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id]);
    Player::factory()->create(['name' => '  SID BREAM  ', 'team_id' => $team->id]);

    expect(findDuplicates())->toHaveCount(1);
});

test('it does not flag players with the same name on different teams', function () {
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();
    Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $teamA->id]);
    Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $teamB->id]);

    expect(findDuplicates())->toHaveCount(0);
});

test('it does not flag a player with no duplicates', function () {
    Player::factory()->create(['name' => 'Unique Player']);

    expect(findDuplicates())->toHaveCount(0);
});

test('it groups players with no team who share a name', function () {
    Player::factory()->create(['name' => 'Free Agent', 'team_id' => null]);
    Player::factory()->create(['name' => 'Free Agent', 'team_id' => null]);

    expect(findDuplicates())->toHaveCount(1);
});

test('it orders each group oldest first', function () {
    $team = Team::factory()->create();
    $newer = Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id, 'created_at' => now()]);
    $older = Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id, 'created_at' => now()->subYear()]);

    expect(findDuplicates()->first()->pluck('id')->all())->toBe([$older->id, $newer->id]);
});

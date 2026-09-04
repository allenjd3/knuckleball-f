<?php

use App\Jobs\ProcessPlayerData;
use App\Models\ImportData;
use App\Models\Player;
use App\Models\Team;

it('marks a player retired even when the retirement value has no parseable year', function () {
    Team::factory()->create(['name' => 'Free Agents']);

    ImportData::factory()->create([
        'data' => [
            'name' => 'Old Timer',
            'address' => "123 Main St\nAnytown\nOH\n45011",
            'team' => 'Free Agents',
            'retired_at' => 'Retired',
        ],
    ]);

    (new ProcessPlayerData)->handle();

    $player = Player::where('name', 'Old Timer')->first();

    expect($player)->not->toBeNull()
        ->and($player->is_retired)->toBeTrue()
        ->and($player->retired_at)->toBeNull();
});

it('still parses a real retirement year when one is given', function () {
    Team::factory()->create(['name' => 'Free Agents']);

    ImportData::factory()->create([
        'data' => [
            'name' => 'Young Timer',
            'address' => "123 Main St\nAnytown\nOH\n45011",
            'team' => 'Free Agents',
            'retired_at' => '1999',
        ],
    ]);

    (new ProcessPlayerData)->handle();

    $player = Player::where('name', 'Young Timer')->first();

    expect($player)->not->toBeNull()
        ->and($player->is_retired)->toBeTrue()
        ->and($player->retired_at->format('Y'))->toBe('1999');
});

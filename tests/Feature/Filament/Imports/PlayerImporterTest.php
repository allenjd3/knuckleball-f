<?php

use App\Filament\Imports\PlayerImporter;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;

beforeEach(function () {
    $this->user = User::factory()->create();
    auth()->login($this->user);

    Team::factory()->create(['name' => 'Seattle Mariners']);

    $this->importer = fn () => new PlayerImporter(
        Import::create([
            'file_name' => 'players.csv',
            'file_path' => 'players.csv',
            'importer' => PlayerImporter::class,
            'total_rows' => 1,
            'user_id' => $this->user->id,
        ]),
        collect(['name', 'team', 'user', 'published_at', 'lastTeam', 'retired_at'])
            ->mapWithKeys(fn (string $column) => [$column => $column])
            ->all(),
        [],
    );

    $this->row = fn (array $overrides = []) => array_merge([
        'name' => 'Ken Griffey Jr.',
        'team' => 'Seattle Mariners',
        'user' => '',
        'published_at' => '',
        'lastTeam' => '',
        'retired_at' => '',
    ], $overrides);
});

it('marks a player retired when a retirement value is provided', function () {
    ($this->importer)()(($this->row)(['retired_at' => '2010']));

    expect(Player::first())
        ->is_retired->toBeTrue();
});

it('leaves a player active when no retirement value is provided', function () {
    ($this->importer)()(($this->row)());

    expect(Player::first())
        ->is_retired->toBeFalse();
});

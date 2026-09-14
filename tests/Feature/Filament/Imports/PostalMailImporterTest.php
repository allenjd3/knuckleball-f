<?php

use App\Filament\Imports\PostalMailImporter;
use App\Models\Feed;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\Team;
use App\Models\User;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::factory()->create();
    auth()->login($this->user);

    $this->importer = fn () => new PostalMailImporter(
        Import::create([
            'file_name' => 'returns.csv',
            'file_path' => 'returns.csv',
            'importer' => PostalMailImporter::class,
            'total_rows' => 1,
            'user_id' => $this->user->id,
        ]),
        collect(['player_name', 'team', 'date_sent', 'returned_date', 'item', 'manufacturer', 'series', 'year', 'number', 'variation', 'comment'])
            ->mapWithKeys(fn (string $column) => [$column => $column])
            ->all(),
        [],
    );

    $this->row = fn (array $overrides = []) => array_merge([
        'player_name' => 'Ken Griffey Jr.',
        'team' => '',
        'date_sent' => '2024-01-01',
        'returned_date' => '',
        'item' => '',
        'manufacturer' => '',
        'series' => '',
        'year' => '',
        'number' => '',
        'variation' => '',
        'comment' => '',
    ], $overrides);
});

it('matches an existing player by exact name', function () {
    $player = Player::factory()->create(['name' => 'Ken Griffey Jr.', 'slug' => Str::slug('Ken Griffey Jr.')]);

    ($this->importer)()(($this->row)());

    expect(Player::count())->toBe(1)
        ->and(PostalMail::first()->signer_id)->toBe($player->signer->id);
});

it('matches a player whose slug only differs by punctuation that slugifies away', function () {
    Player::factory()->create(['name' => 'Ken Griffey, Jr.', 'slug' => Str::slug('Ken Griffey, Jr.')]);

    ($this->importer)()(($this->row)(['player_name' => 'Ken Griffey Jr.']));

    expect(Player::count())->toBe(1);
});

it('does not fuzzy match players with genuinely different names', function () {
    Player::factory()->create(['name' => 'Ken Griffey Sr.', 'slug' => Str::slug('Ken Griffey Sr.')]);

    ($this->importer)()(($this->row)(['player_name' => 'Ken Griffey Jr.']));

    expect(Player::count())->toBe(2);
});

it('does not guess between two players sharing a name, even with a team hint', function () {
    $mariners = Team::factory()->create(['name' => 'Mariners']);
    $reds = Team::factory()->create(['name' => 'Reds']);

    $marinersGriffey = Player::factory()->create(['name' => 'Ken Griffey Jr.', 'slug' => Str::slug('Ken Griffey Jr.'), 'team_id' => $mariners->id]);
    Player::factory()->create(['name' => 'Ken Griffey Jr.', 'slug' => Str::slug('Ken Griffey Jr.') . '-2', 'team_id' => $reds->id]);

    // The exact slug only ever identifies one player, so the "Reds" team hint
    // on the CSV row is not used to try to guess the other one instead.
    ($this->importer)()(($this->row)(['team' => 'Reds']));

    expect(Player::count())->toBe(2)
        ->and(PostalMail::first()->signer_id)->toBe($marinersGriffey->signer->id);
});

it('sets the team on a newly created player when one is given', function () {
    $reds = Team::factory()->create(['name' => 'Reds']);

    ($this->importer)()(($this->row)(['player_name' => 'Someone Totally New', 'team' => 'Reds']));

    expect(Player::first()->team_id)->toBe($reds->id);
});

it('creates an unpublished draft player when no match is found', function () {
    ($this->importer)()(($this->row)(['player_name' => 'Someone Totally New']));

    $player = Player::first();

    expect($player)->not->toBeNull()
        ->and($player->published_at)->toBeNull();
});

it('combines two cards for the same player and date into one postal mail', function () {
    Player::factory()->create(['name' => 'Ken Griffey Jr.', 'slug' => Str::slug('Ken Griffey Jr.')]);

    $importer = ($this->importer)();
    $importer(($this->row)(['manufacturer' => 'Topps', 'series' => '1', 'year' => 1989, 'number' => '1']));
    $importer(($this->row)(['manufacturer' => 'Donruss', 'series' => '1', 'year' => 1990, 'number' => '2']));

    expect(PostalMail::count())->toBe(1)
        ->and(PostalMail::first()->cards)->toHaveCount(2);
});

it('skips a row as a duplicate when the same card was already logged', function () {
    Player::factory()->create(['name' => 'Ken Griffey Jr.', 'slug' => Str::slug('Ken Griffey Jr.')]);

    $importer = ($this->importer)();
    $row = ($this->row)(['manufacturer' => 'Topps', 'series' => '1', 'year' => 1989, 'number' => '1']);

    $importer($row);

    expect(fn () => $importer($row))->toThrow(RowImportFailedException::class);

    expect(PostalMail::count())->toBe(1)
        ->and(PostalMail::first()->cards)->toHaveCount(1);
});

it('does not treat a different variation of the same card as a duplicate', function () {
    Player::factory()->create(['name' => 'Ken Griffey Jr.', 'slug' => Str::slug('Ken Griffey Jr.')]);

    $importer = ($this->importer)();
    $importer(($this->row)(['manufacturer' => 'Topps', 'series' => '1', 'year' => 1989, 'number' => '1', 'variation' => 'Base']));
    $importer(($this->row)(['manufacturer' => 'Topps', 'series' => '1', 'year' => 1989, 'number' => '1', 'variation' => 'Refractor']));

    expect(PostalMail::count())->toBe(1)
        ->and(PostalMail::first()->cards)->toHaveCount(2);
});

it('does not post imported returns to the feed', function () {
    Player::factory()->create(['name' => 'Ken Griffey Jr.', 'slug' => Str::slug('Ken Griffey Jr.')]);

    ($this->importer)()(($this->row)());

    expect(Feed::count())->toBe(0);
});

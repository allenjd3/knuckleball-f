<?php

use App\Filament\Resources\PlayerResource;
use App\Filament\Resources\PlayerResource\Pages\DuplicatePlayers;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\Team;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->admin = User::factory()->isSuperAdmin()->create();
    $this->actingAs($this->admin);
});

it('can render the duplicate players page', function () {
    get(PlayerResource::getUrl('duplicates'))->assertSuccessful();
});

it('lists players that share a name and team as a duplicate group', function () {
    $team = Team::factory()->create();
    $first = Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id]);
    $second = Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id]);
    $unrelated = Player::factory()->create(['name' => 'Nobody Else']);

    Livewire::test(DuplicatePlayers::class)
        ->assertCanSeeTableRecords([$first, $second])
        ->assertCanNotSeeTableRecords([$unrelated]);
});

it('merging keeps the chosen player and folds the other one into it', function () {
    $team = Team::factory()->create();
    $survivor = Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id]);
    $duplicate = Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id]);

    $mail = PostalMail::factory()->create(['signer_id' => $duplicate->signer->id]);

    Livewire::test(DuplicatePlayers::class)
        ->callAction(TestAction::make('mergePlayer')->table($survivor));

    expect(Player::find($duplicate->id))->toBeNull()
        ->and($mail->fresh()->signer_id)->toBe($survivor->signer->id);
});

it('deleting a player removes it and its TTM history without touching its duplicate', function () {
    $team = Team::factory()->create();
    $keeper = Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id]);
    $junk = Player::factory()->create(['name' => 'Sid Bream', 'team_id' => $team->id]);
    $mail = PostalMail::factory()->create(['signer_id' => $junk->signer->id]);

    Livewire::test(DuplicatePlayers::class)
        ->callAction(TestAction::make('deletePlayer')->table($junk));

    expect(Player::find($junk->id))->toBeNull()
        ->and(PostalMail::find($mail->id))->toBeNull()
        ->and(Player::find($keeper->id))->not->toBeNull();
});

it('blocks non admin editors from the page', function () {
    $this->actingAs(User::factory()->create());

    get(PlayerResource::getUrl('duplicates'))->assertRedirect(route('users.feed'));
});

<?php

use App\Livewire\ViewPlayers;
use App\Livewire\ViewPlayersFromCategory;
use App\Livewire\ViewPlayersFromTeam;
use App\Models\Category;
use App\Models\Fee;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Storage;

test('Unauthenticated users can view players', function () {
    $this->get('players')->assertOk();
});

test('the players table shows a placeholder avatar to guests but the real photo to authenticated users', function () {
    // ImageColumn checks file existence against Filament's configured default
    // disk ("local", storage/app/private — not the "public" disk everything
    // else in this app renders through Storage::url()).
    Storage::fake('local');
    Storage::disk('local')->put('avatars/test.jpg', 'fake-image-content');

    $player = Player::factory()->published()->create();
    $player->media()->create(['url' => 'avatars/test.jpg']);

    $this->get('players')
        ->assertDontSee('avatars/test.jpg');

    $this->actingAs(User::factory()->create())
        ->get('players')
        ->assertSee('avatars/test.jpg');
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
        ->assertCanSeeTableRecords($players->sortBy([['last_name', 'asc'], ['name', 'asc']]), inOrder: true);
});

test('players are listed alphabetically by last name by default, not by submission order', function () {
    $players = Player::factory()->published()->create(['name' => 'Zach Adams']);
    $players2 = Player::factory()->published()->create(['name' => 'Alan Ziegler']);

    Livewire::test('ViewPlayers')
        ->assertCanSeeTableRecords([$players, $players2], inOrder: true);
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

test('a player can be marked retired without a retirement year', function () {
    $team = Team::factory()->create();
    $user = User::factory()->isSuperAdmin()->create();

    $player = Player::factory()->published()->create([
        'name' => 'Joe DeScoobio',
        'team_id' => $team->id,
        'retired_at' => null,
        'is_retired' => false,
    ]);

    Livewire::actingAs($user)
        ->test(ViewPlayers::class)
        ->assertTableColumnStateSet('retired_at_status', 'Active', $player)
        ->callAction(TestAction::make('edit')->table($player), ['is_retired' => true])
        ->assertHasNoFormErrors()
        ->assertTableColumnStateSet('retired_at_status', 'Retired', $player->fresh());

    $this->assertDatabaseHas('players', ['id' => $player->id, 'is_retired' => true, 'retired_at' => null]);
});

test('Players cannot be updated by non-users', function () {
    $player = Player::factory()->published()->create();
    Livewire::test('ViewPlayers')->assertActionHidden(TestAction::make('edit')->table($player));
});

test('Creating a player requires a name', function () {
    $user = User::factory()->isSuperAdmin()->create();

    Livewire::actingAs($user)
        ->test(ViewPlayers::class)
        ->callAction('create', ['name' => null, 'team_id' => null])
        ->assertHasActionErrors(['name' => 'required']);
});

test('Editing a player requires a name', function () {
    $user = User::factory()->isSuperAdmin()->create();
    $player = Player::factory()->published()->create();

    Livewire::actingAs($user)
        ->test(ViewPlayers::class)
        ->callAction(TestAction::make('edit')->table($player), ['name' => null])
        ->assertHasActionErrors(['name' => 'required']);
});

test('players can be filtered by team', function () {
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();
    $playerA = Player::factory()->published()->create(['team_id' => $teamA->id]);
    $playerB = Player::factory()->published()->create(['team_id' => $teamB->id]);

    Livewire::test(ViewPlayers::class)
        ->filterTable('team_id', $teamA->id)
        ->assertCanSeeTableRecords([$playerA])
        ->assertCanNotSeeTableRecords([$playerB]);
});

test('players can be filtered by category', function () {
    $categoryA = Category::factory()->create();
    $categoryB = Category::factory()->create();
    $teamA = Team::factory()->create(['category_id' => $categoryA->id]);
    $teamB = Team::factory()->create(['category_id' => $categoryB->id]);
    $playerA = Player::factory()->published()->create(['team_id' => $teamA->id]);
    $playerB = Player::factory()->published()->create(['team_id' => $teamB->id]);

    Livewire::test(ViewPlayers::class)
        ->filterTable('category', $categoryA->id)
        ->assertCanSeeTableRecords([$playerA])
        ->assertCanNotSeeTableRecords([$playerB]);
});

test('players can be filtered by status', function () {
    $active = Player::factory()->published()->create(['is_retired' => false, 'retired_at' => null, 'deceased_at' => null]);
    $retired = Player::factory()->published()->create(['is_retired' => true, 'retired_at' => null, 'deceased_at' => null]);
    $deceased = Player::factory()->published()->create(['is_retired' => false, 'retired_at' => null, 'deceased_at' => now()->subYear()]);

    Livewire::test(ViewPlayers::class)
        ->filterTable('status', 'retired')
        ->assertCanSeeTableRecords([$retired])
        ->assertCanNotSeeTableRecords([$active, $deceased]);

    Livewire::test(ViewPlayers::class)
        ->filterTable('status', 'deceased')
        ->assertCanSeeTableRecords([$deceased])
        ->assertCanNotSeeTableRecords([$active, $retired]);

    Livewire::test(ViewPlayers::class)
        ->filterTable('status', 'active')
        ->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords([$retired, $deceased]);
});

test('players can be filtered by whether they require a fee', function () {
    $withFee = Player::factory()->published()->create();
    Fee::factory()->create(['signer_id' => $withFee->signer->id]);

    $withoutFee = Player::factory()->published()->create();

    Livewire::test(ViewPlayers::class)
        ->filterTable('fees_required')
        ->assertCanSeeTableRecords([$withFee])
        ->assertCanNotSeeTableRecords([$withoutFee]);
});

test('a search query string pre-fills the players table search', function () {
    Player::factory()->published()->create(['name' => 'Bumblebee Guy']);
    Player::factory()->published()->create(['name' => 'Zzyzx Nobody']);

    $this->get('players?search=Bumblebee')
        ->assertSee('Bumblebee Guy')
        ->assertDontSee('Zzyzx Nobody');
});

test('players can be filtered by response rate', function () {
    $highResponder = Player::factory()->published()->create();
    PostalMail::factory()->count(4)->create(['signer_id' => $highResponder->signer->id, 'returned_date' => now(), 'is_failed' => false]);
    PostalMail::factory()->count(1)->create(['signer_id' => $highResponder->signer->id, 'returned_date' => null, 'is_failed' => true]);

    $lowResponder = Player::factory()->published()->create();
    PostalMail::factory()->count(1)->create(['signer_id' => $lowResponder->signer->id, 'returned_date' => now(), 'is_failed' => false]);
    PostalMail::factory()->count(4)->create(['signer_id' => $lowResponder->signer->id, 'returned_date' => null, 'is_failed' => true]);

    Livewire::test(ViewPlayers::class)
        ->filterTable('response_rate', 'high')
        ->assertCanSeeTableRecords([$highResponder])
        ->assertCanNotSeeTableRecords([$lowResponder]);

    Livewire::test(ViewPlayers::class)
        ->filterTable('response_rate', 'low')
        ->assertCanSeeTableRecords([$lowResponder])
        ->assertCanNotSeeTableRecords([$highResponder]);
});

test('pending postal mails do not count against response rate', function () {
    $player = Player::factory()->published()->create();
    PostalMail::factory()->count(1)->create(['signer_id' => $player->signer->id, 'returned_date' => now(), 'is_failed' => false]);
    PostalMail::factory()->count(10)->create(['signer_id' => $player->signer->id, 'returned_date' => null, 'is_failed' => false]);

    Livewire::test(ViewPlayers::class)
        ->filterTable('response_rate', 'high')
        ->assertCanSeeTableRecords([$player]);
});

test('players can be filtered by tag', function () {
    $tag = Tag::factory()->create(['label' => 'Fast Responder']);
    $tagged = Player::factory()->published()->create();
    $tagged->signer->tags()->attach($tag->id, ['approved_at' => now(), 'user_id' => User::factory()->create()->id]);

    $untagged = Player::factory()->published()->create();

    Livewire::test(ViewPlayers::class)
        ->filterTable('tags', [$tag->id])
        ->assertCanSeeTableRecords([$tagged])
        ->assertCanNotSeeTableRecords([$untagged]);
});

test('unapproved tags do not count for the tags filter', function () {
    $tag = Tag::factory()->create();
    $player = Player::factory()->published()->create();
    $player->signer->tags()->attach($tag->id, ['approved_at' => null, 'user_id' => User::factory()->create()->id]);

    Livewire::test(ViewPlayers::class)
        ->filterTable('tags', [$tag->id])
        ->assertCanNotSeeTableRecords([$player]);
});

test('players can be filtered by whether they have a photo', function () {
    $withPhoto = Player::factory()->published()->create();
    $withPhoto->media()->create(['url' => 'avatars/test.jpg']);

    $withoutPhoto = Player::factory()->published()->create();

    Livewire::test(ViewPlayers::class)
        ->filterTable('has_photo')
        ->assertCanSeeTableRecords([$withPhoto])
        ->assertCanNotSeeTableRecords([$withoutPhoto]);
});

test('players can be filtered by recent activity', function () {
    $active = Player::factory()->published()->create();
    PostalMail::factory()->create(['signer_id' => $active->signer->id, 'returned_date' => now()->subDays(10)]);

    $inactive = Player::factory()->published()->create();
    PostalMail::factory()->create(['signer_id' => $inactive->signer->id, 'returned_date' => now()->subDays(200)]);

    Livewire::test(ViewPlayers::class)
        ->filterTable('recently_active')
        ->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords([$inactive]);
});

test('the players page shows an All tab plus one tab per category, ordered alphabetically', function () {
    Category::query()->delete();
    Category::factory()->create(['name' => 'Football']);
    Category::factory()->create(['name' => 'Baseball']);

    Livewire::test(ViewPlayers::class)
        ->assertSeeHtmlInOrder(['All', 'Baseball', 'Football']);
});

test('players can be filtered by category tab', function () {
    $categoryA = Category::factory()->create();
    $categoryB = Category::factory()->create();
    $teamA = Team::factory()->create(['category_id' => $categoryA->id]);
    $teamB = Team::factory()->create(['category_id' => $categoryB->id]);
    $playerA = Player::factory()->published()->create(['team_id' => $teamA->id]);
    $playerB = Player::factory()->published()->create(['team_id' => $teamB->id]);

    Livewire::test(ViewPlayers::class)
        ->call('setCategoryTab', $categoryA->id)
        ->assertCanSeeTableRecords([$playerA])
        ->assertCanNotSeeTableRecords([$playerB])
        ->call('setCategoryTab', null)
        ->assertCanSeeTableRecords([$playerA, $playerB]);
});

test('category tabs are hidden on category- and team-scoped player pages', function () {
    $category = Category::factory()->create();
    $team = Team::factory()->create(['category_id' => $category->id]);

    Livewire::test(ViewPlayersFromCategory::class, ['category' => $category])
        ->assertDontSeeHtml('wire:click="setCategoryTab(null)"');

    Livewire::test(ViewPlayersFromTeam::class, ['team' => $team->id])
        ->assertDontSeeHtml('wire:click="setCategoryTab(null)"');
});

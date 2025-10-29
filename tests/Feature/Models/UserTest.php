<?php

use App\Enums\Role;
use App\Models\Address;
use App\Models\Card;
use App\Models\Comment;
use App\Models\Fee;
use App\Models\Feed;
use App\Models\ImportData;
use App\Models\Player;
use App\Models\PlayerTag;
use App\Models\SignerTag;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('it generates a slug for new users', function () {
    $user = User::factory()->create();

    expect($user->slug)->toStartWith(str()->slug($user->name, '-'));
});

test('it generates a slug for old users', function () {
    $user = User::factory()->make();

    DB::table('users')
        ->insert(
            $user->only([
                'name',
                'email',
                'email_verified_at',
                'current_team_id',
                'published_at',
                'password',
            ])
        );

    $user = User::first();
    expect($user->slug)->toBeNull();

    $this->artisan('users:slug-generate')
        ->expectsConfirmation('This will reset all User Slugs. Only run this once! Do you wish to continue?', 'yes')
        ->assertExitCode(0);

    expect($user->fresh()->slug)->toStartWith(str()->slug($user->name, '-'));
});

test('a user can follow another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->follow($user2);

    expect($user2->followers->pluck('id'))->toContain($user1->id);
});

test('a user can only follow another user once', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->follow($user2);
    $user1->follow($user2);

    $this->assertCount(1, $user1->following);
});

test('a user can unfollow another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->follow($user2);
    expect($user2->followers->pluck('id'))->toContain($user1->id);

    $user1->unfollow($user2);
    expect($user2->fresh()->followers->pluck('id'))->toBeEmpty();
});

test('a user can get a list of all of the people following them', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    User::factory(2)->create();

    $user1->follow($user2);
    $user3->follow($user2);

    expect($user2->followers->pluck('id')->toArray())->toEqual([$user1->id, $user3->id]);
});

test('a user can get a list of everyone they are following', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    User::factory(2)->create();

    $user1->follow($user2);
    $user1->follow($user3);

    expect($user1->following->pluck('id')->toArray())->toEqual([$user2->id, $user3->id]);
});

test('users have a default role', function () {
    $user = User::factory()->create();

    expect($user->fresh()->role)->toEqual(Role::USER);
});

test('users can be set to admin', function () {
    $user = User::factory()->create();
    $user->role = Role::ADMIN;

    $user->save();

    expect($user->fresh()->role)->toEqual(Role::ADMIN);
});

test('editors cannot update users', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $this->assertFalse($editor->can('viewAny', User::class));
});

test('editors cannot import datas', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $this->assertFalse($editor->can('viewAny', ImportData::class));
});

test('editors can edit and delete addresses', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $address = Address::factory()->create();
    $this->assertTrue($editor->can('update', $address));
    $this->assertTrue($editor->can('delete', $address));
});

test('editors can delete cards', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $card = Card::factory()->for(User::factory())->create();
    $this->assertTrue($editor->can('delete', $card));
});

test('editors can delete comments', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $comment = Comment::factory()->create();

    $this->assertTrue($editor->can('delete', $comment));
});

test('editors can delete Fees', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $fee = Fee::factory()->create();

    $this->assertTrue($editor->can('delete', $fee));
});

test('editors can delete feeds', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $feed = Feed::factory()->postalMail()->create();

    $this->assertTrue($editor->can('delete', $feed));
});

test('editors cannot view importdatas', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $this->assertFalse($editor->can('viewAny', ImportData::class));
});

test('editors can delete and manage players', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $player = Player::factory()->create();

    $this->assertTrue($editor->can('delete', $player));
    $this->assertTrue($editor->can('manage', $player));
});

test('editors can update and delete player tags', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $playerTag = new PlayerTag;

    $this->assertTrue($editor->can('delete', $playerTag));
    $this->assertTrue($editor->can('update', $playerTag));
});

test('editors can update and delete signer tags', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $signerTag = new SignerTag;

    $this->assertTrue($editor->can('delete', $signerTag));
    $this->assertTrue($editor->can('update', $signerTag));
});

test('editors can create update and delete tags', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $tag = Tag::factory()->create();

    $this->assertTrue($editor->can('create', $tag));
    $this->assertTrue($editor->can('delete', $tag));
    $this->assertTrue($editor->can('update', $tag));
});

test('editors can delete teams', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();
    $tag = Team::factory()->create();

    $this->assertTrue($editor->can('delete', $tag));
});

test('editors cannot update or delete users', function () {
    $editor = User::factory()->state(['role' => Role::EDITOR])->create();

    $this->assertFalse($editor->can('viewAny', User::class));
});
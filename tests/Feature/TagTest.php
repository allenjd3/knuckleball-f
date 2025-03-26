<?php

use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Livewire\ShowPlayer;
use App\Models\Player;
use App\Models\PlayerTag;
use App\Models\Tag;
use App\Models\User;

test('a published user can associate tag with player', function () {
    $user = User::factory()->create(['published_at' => now()]);
    $player = Player::factory()->create();
    $tag = Tag::factory()->create(['published_at' => now()]);

    $this->actingAs($user);

    Livewire::test(ShowPlayer::class, ['player' => $player])
        ->call('addTag', $tag->id);

    $this->assertDatabaseHas('player_tag', [
        'player_id' => $player->id,
        'tag_id' => $tag->id,
        'approved_at' => null, // Not yet approved
    ]);
});

test('an unpublished user cannot associate tag with player', function () {
    $user = User::factory()->create(['published_at' => null]);
    $player = Player::factory()->create();
    $tag = Tag::factory()->create(['published_at' => now()]);

    $this->actingAs($user);

    Livewire::test(ShowPlayer::class, ['player' => $player])
        ->call('addTag', $tag->id);

    $this->assertDatabaseMissing('player_tag', [
        'player_id' => $player->id,
        'tag_id' => $tag->id,
    ]);
});

test('an admin can approve tag player association', function () {

    $admin = User::factory()->create(['super_admin' => true]);
    $player = Player::factory()->create();
    $tag = Tag::factory()->create(['published_at' => now()]);
    $playerTag = PlayerTag::create(['player_id' => $player->id, 'tag_id' => $tag->id]);

    $this->freezeTime(function () use ($player, $tag, $admin, $playerTag) {
        $admin->approveTag($playerTag);

        $this->assertDatabaseHas('player_tag', [
            'player_id' => $player->id,
            'tag_id' => $tag->id,
            'approved_at' => now()->toDateTimeString(),
        ]);
    });
});

// A non-admin cannot approve tag player association
test('a non admin cannot approve tag player association', function () {
    $regularUser = User::factory()->create(['super_admin' => false]);
    $player = Player::factory()->create();
    $tag = Tag::factory()->create(['published_at' => now()]);
    $playerTag = PlayerTag::create(['player_id' => $player->id, 'tag_id' => $tag->id]);

    $this->freezeTime(function () use ($player, $tag, $regularUser, $playerTag) {
        $regularUser->approveTag($playerTag);

        $this->assertDatabaseMissing('player_tag', [
            'player_id' => $player->id,
            'tag_id' => $tag->id,
            'approved_at' => now()->toDateTimeString(),
        ]);
    });
});

// Only superadmin can create tags
test('only superadmin can create tags', function () {
    $superAdmin = User::factory()->create(['super_admin' => true]);
    $regularUser = User::factory()->create(['super_admin' => false]);
    $randomCategory = array_keys(Tag::categories())[rand(0, 3)];

    $this->actingAs($superAdmin);
    Livewire::test(CreateTag::class)
        ->fillForm(['label' => 'Hall of Famer', 'category' => $randomCategory])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('tags', ['label' => 'Hall of Famer']);

    $this->actingAs($regularUser);
    Livewire::test(CreateTag::class)
        ->assertForbidden();

    $this->assertDatabaseMissing('tags', ['label' => 'Fan Favorite']);
});

// Tag should have required attributes
test('tag requires label', function () {
    $superAdmin = User::factory()->create(['super_admin' => true]);

    $this->actingAs($superAdmin);
    Livewire::test(CreateTag::class)
        ->fillForm(['label' => null])
        ->call('create')
        ->assertHasFormErrors(['label']);
});

// Tags are categorized correctly
test('tags are categorized correctly', function () {
    $positiveTag = Tag::factory()->create([
        'label' => 'Verified',
        'category' => 'positive'
    ]);

    $neutralTag = Tag::factory()->create([
        'label' => 'Sticker Shock',
        'category' => 'neutral'
    ]);

    $unpredictableTag = Tag::factory()->create([
        'label' => 'The Wild Card',
        'category' => 'unpredictable'
    ]);

    $negativeTag = Tag::factory()->create([
        'label' => 'Elusive Signer',
        'category' => 'negative'
    ]);

    expect($positiveTag->category)->toBe('positive');
    expect($neutralTag->category)->toBe('neutral');
    expect($unpredictableTag->category)->toBe('unpredictable');
    expect($negativeTag->category)->toBe('negative');
});

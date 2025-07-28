<?php

use App\Livewire\ShowPlayer;
use App\Models\Player;
use App\Models\SignerTag;
use App\Models\Tag;
use App\Models\User;

test('a published user can associate tag with player', function () {
    $user = User::factory()->create(['published_at' => now()]);
    $player = Player::factory()->create();
    $tag = Tag::factory()->create(['published_at' => now()]);

    $this->actingAs($user);

    Livewire::test(ShowPlayer::class, ['player' => $player])
        ->callAction('associateTag', ['tag_id' => $tag->id]);

    $this->assertDatabaseHas('signer_tag', [
        'signer_id' => $player->signer->id,
        'tag_id' => $tag->id,
        'user_id' => $user->id,
        'approved_at' => null, // Not yet approved
    ]);
});

test('an unpublished user cannot associate tag with player', function () {
    $user = User::factory()->create(['published_at' => null]);
    $player = Player::factory()->create();

    $this->actingAs($user);

    Livewire::test(ShowPlayer::class, ['player' => $player])
        ->assertActionHidden('associateTag');
});

test('an admin can approve tag player association', function () {

    $admin = User::factory()->create(['super_admin' => true]);
    $player = Player::factory()->create();
    $tag = Tag::factory()->create(['published_at' => now()]);
    $playerTag = SignerTag::create(['signer_id' => $player->signer->id, 'tag_id' => $tag->id, 'user_id' => $admin->id]);

    $this->freezeTime(function () use ($player, $tag, $admin, $playerTag) {
        $admin->approveTag($playerTag);

        $this->assertDatabaseHas('signer_tag', [
            'signer_id' => $player->signer->id,
            'tag_id' => $tag->id,
            'user_id' => $admin->id,
            'approved_at' => now()->toDateTimeString(),
        ]);
    });
});

test('an admin can reject tag player association', function () {
    $admin = User::factory()->create(['super_admin' => true]);
    $player = Player::factory()->create();
    $tag = Tag::factory()->create(['published_at' => now()]);
    $signerTag = SignerTag::create(['signer_id' => $player->signer->id, 'tag_id' => $tag->id, 'user_id' => $admin->id]);

    $this->freezeTime(function () use ($player, $tag, $admin, $signerTag) {
        $admin->rejectTag($signerTag);

        $this->assertDatabaseMissing('signer_tag', [
            'signer_id' => $player->signer->id,
            'tag_id' => $tag->id,
            'user_id' => $admin->id,
        ]);
    });
});

// A non-admin cannot approve tag player association
test('a non admin cannot approve tag player association', function () {
    $regularUser = User::factory()->create(['super_admin' => false]);
    $player = Player::factory()->create();
    $tag = Tag::factory()->create(['published_at' => now()]);
    $signerTag = SignerTag::create(['signer_id' => $player->id, 'tag_id' => $tag->id, 'user_id' => $regularUser->id]);

    $this->freezeTime(function () use ($player, $tag, $regularUser, $signerTag) {
        $regularUser->approveTag($signerTag);

        $this->assertDatabaseMissing('signer_tag', [
            'signer_id' => $player->signer->id,
            'tag_id' => $tag->id,
            'approved_at' => now()->toDateTimeString(),
            'user_id' => $regularUser->id,
        ]);
    });
});

test('a non admin cannot reject a player tag', function () {
    $regularUser = User::factory()->create(['super_admin' => false]);
    $player = Player::factory()->create();
    $tag = Tag::factory()->create(['published_at' => now()]);
    $signerTag = SignerTag::create(['signer_id' => $player->signer->id, 'tag_id' => $tag->id, 'user_id' => $regularUser->id]);

    $this->freezeTime(function () use ($player, $tag, $regularUser, $signerTag) {
        $regularUser->rejectTag($signerTag);

        $this->assertDatabaseHas('signer_tag', [
            'signer_id' => $player->signer->id,
            'tag_id' => $tag->id,
            'user_id' => $regularUser->id,
            'approved_at' => null,
        ]);
    });
});

// Tags are categorized correctly
test('tags are categorized correctly', function () {
    $positiveTag = Tag::factory()->create([
        'label' => 'Verified',
        'category' => 'positive',
    ]);

    $neutralTag = Tag::factory()->create([
        'label' => 'Sticker Shock',
        'category' => 'neutral',
    ]);

    $unpredictableTag = Tag::factory()->create([
        'label' => 'The Wild Card',
        'category' => 'unpredictable',
    ]);

    $negativeTag = Tag::factory()->create([
        'label' => 'Elusive Signer',
        'category' => 'negative',
    ]);

    expect($positiveTag->category)->toBe('positive');
    expect($neutralTag->category)->toBe('neutral');
    expect($unpredictableTag->category)->toBe('unpredictable');
    expect($negativeTag->category)->toBe('negative');
});

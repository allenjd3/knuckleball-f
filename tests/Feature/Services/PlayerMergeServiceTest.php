<?php

use App\Models\Address;
use App\Models\CardSet;
use App\Models\Event;
use App\Models\Fee;
use App\Models\InPersonAutograph;
use App\Models\Media;
use App\Models\Pack;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\SetEntry;
use App\Models\Signer;
use App\Models\Tag;
use App\Models\User;
use App\Models\WantList;
use App\Services\PlayerMergeService;

function mergeService(): PlayerMergeService
{
    return app(PlayerMergeService::class);
}

test('it throws when merging a player into itself', function () {
    $player = Player::factory()->create();

    mergeService()->merge($player, $player);
})->throws(InvalidArgumentException::class);

test('it moves TTM mail, addresses, and fees from the duplicate to the survivor', function () {
    $survivor = Player::factory()->create();
    $duplicate = Player::factory()->create();

    $mail = PostalMail::factory()->create(['signer_id' => $duplicate->signer->id]);
    $address = Address::factory()->create(['signer_id' => $duplicate->signer->id]);
    $fee = Fee::factory()->create(['signer_id' => $duplicate->signer->id]);

    mergeService()->merge($survivor, $duplicate);

    expect($mail->fresh()->signer_id)->toBe($survivor->signer->id)
        ->and($address->fresh()->signer_id)->toBe($survivor->signer->id)
        ->and($fee->fresh()->signer_id)->toBe($survivor->signer->id)
        ->and(Player::find($duplicate->id))->toBeNull()
        ->and(Signer::find($duplicate->signer->id))->toBeNull();
});

test('it moves in-person autographs from the duplicate to the survivor', function () {
    $survivor = Player::factory()->create();
    $duplicate = Player::factory()->create();

    $autograph = InPersonAutograph::factory()->create(['signer_id' => $duplicate->signer->id]);

    mergeService()->merge($survivor, $duplicate);

    expect($autograph->fresh()->signer_id)->toBe($survivor->signer->id);
});

test('it merges tags without duplicating ones the survivor already has', function () {
    $survivor = Player::factory()->create();
    $duplicate = Player::factory()->create();

    $sharedTag = Tag::factory()->create();
    $uniqueTag = Tag::factory()->create();
    $user = User::factory()->create();

    $survivor->signer->tags()->attach($sharedTag->id, ['user_id' => $user->id]);
    $duplicate->signer->tags()->attach($sharedTag->id, ['user_id' => $user->id]);
    $duplicate->signer->tags()->attach($uniqueTag->id, ['user_id' => $user->id]);

    mergeService()->merge($survivor, $duplicate);

    expect($survivor->signer->tags()->pluck('tags.id')->sort()->values()->all())
        ->toBe([$sharedTag->id, $uniqueTag->id]);
});

test('it moves the duplicate photo to the survivor when the survivor has none', function () {
    $survivor = Player::factory()->create();
    $duplicate = Player::factory()->create();
    $media = Media::factory()->create(['imageable_id' => $duplicate->id, 'imageable_type' => $duplicate->getMorphClass()]);

    mergeService()->merge($survivor, $duplicate);

    expect($media->fresh()->imageable_id)->toBe($survivor->id)
        ->and($media->fresh()->imageable_type)->toBe($survivor->getMorphClass());
});

test('it keeps the survivor own photo and discards the duplicate photo instead of leaving it orphaned', function () {
    $survivor = Player::factory()->create();
    $survivorMedia = Media::factory()->create(['imageable_id' => $survivor->id, 'imageable_type' => $survivor->getMorphClass()]);

    $duplicate = Player::factory()->create();
    $duplicateMedia = Media::factory()->create(['imageable_id' => $duplicate->id, 'imageable_type' => $duplicate->getMorphClass()]);

    mergeService()->merge($survivor, $duplicate);

    expect($survivorMedia->fresh()->imageable_id)->toBe($survivor->id)
        ->and(Media::find($duplicateMedia->id))->toBeNull();
});

test('it reassigns packs, want lists, and watchlists, dropping ones the survivor already has', function () {
    $survivor = Player::factory()->create();
    $duplicate = Player::factory()->create();

    $packOwner = User::factory()->create();
    $sharedPack = Pack::create(['user_id' => $packOwner->id, 'name' => 'Shared Pack', 'is_public' => true]);
    $onlyOnDuplicatePack = Pack::create(['user_id' => $packOwner->id, 'name' => 'Duplicate Only Pack', 'is_public' => true]);
    $sharedPack->addPlayer($survivor);
    $sharedPack->addPlayer($duplicate);
    $onlyOnDuplicatePack->addPlayer($duplicate);

    $wantListOwner = User::factory()->create();
    $wantList = WantList::create(['user_id' => $wantListOwner->id, 'name' => 'A Want List', 'is_public' => true]);
    $wantList->addPlayer($duplicate);

    $watcher = User::factory()->create();
    $watcher->watchlist()->attach($duplicate->id);

    mergeService()->merge($survivor, $duplicate);

    expect($sharedPack->players()->pluck('players.id')->all())->toBe([$survivor->id])
        ->and($onlyOnDuplicatePack->players()->pluck('players.id')->all())->toBe([$survivor->id])
        ->and($wantList->players()->pluck('players.id')->all())->toBe([$survivor->id])
        ->and($watcher->watchlist()->pluck('players.id')->all())->toBe([$survivor->id]);
});

test('it reassigns set entries and events to the survivor', function () {
    $survivor = Player::factory()->create();
    $duplicate = Player::factory()->create();

    $cardSet = CardSet::create(['user_id' => User::factory()->create()->id, 'name' => 'A Set', 'is_public' => true]);
    $entry = SetEntry::create(['set_id' => $cardSet->id, 'player_id' => $duplicate->id]);

    $event = Event::factory()->playerSigning($duplicate)->create();
    $event->expectedSigners()->attach($duplicate->id);

    mergeService()->merge($survivor, $duplicate);

    expect($entry->fresh()->player_id)->toBe($survivor->id)
        ->and($event->fresh()->player_id)->toBe($survivor->id)
        ->and($event->expectedSigners()->pluck('players.id')->all())->toBe([$survivor->id]);
});

test('delete purges a player along with its TTM mail, in-person autographs, addresses, fees, and tags', function () {
    $player = Player::factory()->create();
    $signerId = $player->signer->id;

    PostalMail::factory()->create(['signer_id' => $signerId]);
    Address::factory()->create(['signer_id' => $signerId]);
    Fee::factory()->create(['signer_id' => $signerId]);
    InPersonAutograph::factory()->create(['signer_id' => $signerId]);
    $player->signer->tags()->attach(Tag::factory()->create()->id, ['user_id' => User::factory()->create()->id]);

    mergeService()->delete($player);

    expect(Player::find($player->id))->toBeNull()
        ->and(Signer::find($signerId))->toBeNull()
        ->and(PostalMail::where('signer_id', $signerId)->count())->toBe(0)
        ->and(Address::where('signer_id', $signerId)->count())->toBe(0)
        ->and(Fee::where('signer_id', $signerId)->count())->toBe(0)
        ->and(InPersonAutograph::where('signer_id', $signerId)->count())->toBe(0);
});

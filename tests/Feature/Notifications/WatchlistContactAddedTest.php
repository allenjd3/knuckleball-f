<?php

use App\Actions\RequestAddress;
use App\Enums\AddressRequestReason;
use App\Enums\AddressType;
use App\Models\Address;
use App\Models\Player;
use App\Models\User;
use App\Notifications\AddressRequestFulfilled;
use App\Notifications\WatchlistContactAdded;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

test('watchers are told when a watched player gets a new mailing address', function () {
    $player = Player::factory()->create();
    $watcher = User::factory()->create();
    $watcher->watchlist()->attach($player->id);
    $bystander = User::factory()->create();

    Address::factory()->published()->create(['signer_id' => $player->signer->id]);

    Notification::assertSentTo($watcher, WatchlistContactAdded::class, fn (WatchlistContactAdded $notification) => $notification->contactType === AddressType::Mail
        && $notification->player->is($player));
    Notification::assertNotSentTo($bystander, WatchlistContactAdded::class);
});

test('watchers are told when a watched player starts taking email requests', function () {
    $player = Player::factory()->create();
    $watcher = User::factory()->create();
    $watcher->watchlist()->attach($player->id);

    Address::factory()->published()->email()->create(['signer_id' => $player->signer->id]);

    Notification::assertSentTo($watcher, WatchlistContactAdded::class, fn (WatchlistContactAdded $notification) => $notification->contactType === AddressType::Email);
});

test('watchers are not told about unpublished addresses until they are published', function () {
    $player = Player::factory()->create();
    $watcher = User::factory()->create();
    $watcher->watchlist()->attach($player->id);

    $address = Address::factory()->create(['signer_id' => $player->signer->id]);

    Notification::assertNotSentTo($watcher, WatchlistContactAdded::class);

    $address->update(['published_at' => now()->subMinute()]);

    Notification::assertSentTo($watcher, WatchlistContactAdded::class);
});

test('a watcher who requested the address only gets the request notification', function () {
    $player = Player::factory()->create();
    $watcher = User::factory()->create();
    $watcher->watchlist()->attach($player->id);
    RequestAddress::execute($watcher, $player->signer, AddressRequestReason::MissingAddress);

    Address::factory()->published()->create(['signer_id' => $player->signer->id]);

    Notification::assertSentTo($watcher, AddressRequestFulfilled::class);
    Notification::assertNotSentTo($watcher, WatchlistContactAdded::class);
});

test('the watcher who submitted the address is not notified about it', function () {
    $player = Player::factory()->create();
    $submitter = User::factory()->create();
    $submitter->watchlist()->attach($player->id);

    Address::factory()->published()->create(['signer_id' => $player->signer->id, 'user_id' => $submitter->id]);

    Notification::assertNotSentTo($submitter, WatchlistContactAdded::class);
});

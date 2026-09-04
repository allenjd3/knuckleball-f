<?php

use App\Models\Address;
use App\Models\Player;

test('an expired address is skipped in favor of the next active one', function () {
    $player = Player::factory()->create();

    $olderActive = Address::factory()->published()->create([
        'signer_id' => $player->signer->id,
        'created_at' => now()->subWeek(),
    ]);

    Address::factory()->published()->create([
        'signer_id' => $player->signer->id,
        'created_at' => now()->subDay(),
        'expires_at' => now()->subHour(),
    ]);

    expect($player->address()->id)->toBe($olderActive->id);
});

test('a non-expired address with no expiration is used', function () {
    $player = Player::factory()->create();

    $address = Address::factory()->published()->create([
        'signer_id' => $player->signer->id,
        'expires_at' => null,
    ]);

    expect($player->address()->id)->toBe($address->id);
});

test('a future-dated expiration does not hide the address yet', function () {
    $player = Player::factory()->create();

    $address = Address::factory()->published()->create([
        'signer_id' => $player->signer->id,
        'expires_at' => now()->addWeek(),
    ]);

    expect($player->address()->id)->toBe($address->id);
});

test('no address is returned when the only one has expired', function () {
    $player = Player::factory()->create();

    Address::factory()->published()->create([
        'signer_id' => $player->signer->id,
        'expires_at' => now()->subDay(),
    ]);

    expect($player->address())->toBeNull();
});

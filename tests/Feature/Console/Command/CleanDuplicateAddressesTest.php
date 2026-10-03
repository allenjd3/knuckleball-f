<?php

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\Player;

test('it cleans up the duplicate addresses', function () {
    $player = Player::factory()->has(Address::factory(5)->published())->create();
    $this->artisan('address:clean-duplicates');

    $this->assertCount(1, $player->addresses);
});

test('it keeps the current email contact alongside the current mailing address', function () {
    $player = Player::factory()->has(Address::factory(3)->published())->create();
    Address::factory(2)->published()->email()->create(['signer_id' => $player->signer->id]);

    $this->artisan('address:clean-duplicates');

    $addresses = $player->addresses()->get();

    expect($addresses)->toHaveCount(2)
        ->and($addresses->pluck('type')->all())->toEqualCanonicalizing([AddressType::Mail, AddressType::Email]);
});

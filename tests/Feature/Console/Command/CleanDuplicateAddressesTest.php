<?php

use App\Models\Address;
use App\Models\Player;

test('it cleans up the duplicate addresses', function () {
    $player = Player::factory()->has(Address::factory(5)->published(), 'addresses')->create();
    $this->artisan('address:clean-duplicates');

    $this->assertCount(1, $player->addresses);
});

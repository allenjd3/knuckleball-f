<?php

use App\Models\Player;

test('it can publish all', function () {
    $players = Player::factory(10)->state(['published_at' => null])->create();

    $firstFive = $players->take(5);

    $firstFive->publishAll();

    expect($firstFive->fresh()
        ->pluck('published_at')
        ->reduce(
            fn ($carry, $publishedAt) => $carry && ! is_null($publishedAt), true)
    )
        ->toBe(true);
});

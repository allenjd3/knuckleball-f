<?php

use App\Models\Fee;
use App\Models\Player;
use App\Models\PostalMail;

test('it can gives a null when less than 3 responses', function () {
    $player = Player::factory()->has(PostalMail::factory())->create();
    expect($player->response_rate)->toBeNull();
});

test('it gives response rate after 3 responses', function () {
    $player = Player::factory()->has(PostalMail::factory(4)->returned())->create();
    expect($player->response_rate)->toBe('100%');
});

test('it calculates the correct response rate', function ($total, $returned, $percent) {
    $player = Player::factory()->create();

    PostalMail::factory($returned)->state(['player_id' => $player->id])->returned()->create();
    PostalMail::factory($total - $returned)->state(['player_id' => $player->id])->unReturned()->create();
    expect($player->response_rate)->toBe($percent);
})->with([[5, 3, '60%'], [6, 5, '83%'], [2, 2, null]]);

test('it can show that fees are required', function () {
    $player = Player::factory()->has(Fee::factory())->create();
    $playerWithoutFees = Player::factory()->create();

    expect($player->fees_required)->toBeTrue();
    expect($playerWithoutFees->fees_required)->toBeFalse();
});

<?php

use App\Models\Fee;
use App\Models\InPersonAutograph;
use App\Models\Player;
use App\Models\PostalMail;

test('it can gives response', function () {
    $player = Player::factory()->has(PostalMail::factory()->unReturned())->create();
    expect($player->response_rate)->toBe('1 is pending.');
});

test('it gives response rate after 3 responses', function () {
    $player = Player::factory()->has(PostalMail::factory(4)->returned())->create();
    expect($player->response_rate)->toBe('100% successful. 0 are pending.');
});

test('it calculates the correct response rate', function ($total, $returned, $percent) {
    $player = Player::factory()->create();

    PostalMail::factory($returned)->state(['signer_id' => $player->signer->id])->returned()->create();
    PostalMail::factory($total - $returned)->state(['signer_id' => $player->signer->id])->unReturned()->create();
    expect($player->response_rate)->toBe($percent);
})->with([[5, 3, '100% successful. 2 are pending.'], [6, 5, '100% successful. 1 is pending.'], [2, 2, '100% successful. 0 are pending.']]);

test('it shows when a response fails', function () {
    $player = Player::factory()->create();

    PostalMail::factory()->state(['signer_id' => $player->signer->id])->failed()->create();
    PostalMail::factory()->state(['signer_id' => $player->signer->id])->returned()->create();
    PostalMail::factory()->state(['signer_id' => $player->signer->id])->unReturned()->create();

    expect($player->response_rate)->toBe('50% successful. 1 is pending.');
});

test('it can show that fees are required', function () {
    $player = Player::factory()->create();
    Fee::factory()->state([
        'signer_id' => $player->signer->id,
    ])->create();

    $playerWithoutFees = Player::factory()->create();

    expect($player->fees_required)->toBeTrue();
    expect($playerWithoutFees->fees_required)->toBeFalse();
});

test('it has no in-person response rate until an autograph is logged', function () {
    $player = Player::factory()->create();

    expect($player->in_person_response_rate)->toBe('');
});

test('it calculates the in-person response rate from obtained vs declined autographs', function () {
    $player = Player::factory()->create();

    InPersonAutograph::factory(3)->create(['signer_id' => $player->signer->id, 'is_declined' => false]);
    InPersonAutograph::factory(1)->create(['signer_id' => $player->signer->id, 'is_declined' => true]);

    expect($player->in_person_response_rate)->toBe('75% obtained in person (3 of 4).');
});

test('last_name is derived from name on save, for sorting the players table alphabetically by last name', function () {
    $player = Player::factory()->create(['name' => 'Sid Bream']);
    expect($player->last_name)->toBe('Bream');

    $player->update(['name' => 'Cal Ripken Jr.']);
    expect($player->fresh()->last_name)->toBe('Ripken');

    $singleName = Player::factory()->create(['name' => 'Pele']);
    expect($singleName->last_name)->toBe('Pele');
});

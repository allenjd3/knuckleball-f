<?php

use App\Livewire\CompareAutographs;
use App\Models\Card;
use App\Models\InPersonAutograph;
use App\Models\Media;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\User;
use Livewire\Livewire;

test('it shows nothing when the player has no photos at all', function () {
    $player = Player::factory()->create();

    Livewire::test(CompareAutographs::class, ['player' => $player])
        ->assertDontSee('Compare Autographs');
});

test('it shows TTM and in-person photos side by side', function () {
    $player = Player::factory()->create();

    $postalMail = PostalMail::factory()->create(['signer_id' => $player->signer->id]);
    $card = Card::factory()->create(['postal_mail_id' => $postalMail->id, 'user_id' => User::factory()]);
    Media::factory()->create(['imageable_id' => $card->id, 'imageable_type' => Card::class, 'url' => 'ttm-photo.jpg']);

    $autograph = InPersonAutograph::factory()->create(['signer_id' => $player->signer->id]);
    $autograph->media()->create(['url' => 'in-person-photo.jpg']);

    Livewire::test(CompareAutographs::class, ['player' => $player])
        ->assertSee('Compare Autographs')
        ->assertSee('ttm-photo.jpg', false)
        ->assertSee('in-person-photo.jpg', false);
});

test('it shows an empty message for whichever side has no photos', function () {
    $player = Player::factory()->create();

    $autograph = InPersonAutograph::factory()->create(['signer_id' => $player->signer->id]);
    $autograph->media()->create(['url' => 'in-person-photo.jpg']);

    Livewire::test(CompareAutographs::class, ['player' => $player])
        ->assertSee('No TTM photos yet')
        ->assertSee('in-person-photo.jpg', false);
});

<?php

use App\Actions\CreateFeedItem;
use App\Enums\SendMethod;
use App\Livewire\FeedComposer;
use App\Livewire\ShowPlayer;
use App\Livewire\UserProfile;
use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

test('sends default to mail', function () {
    $postalMail = PostalMail::factory()->create();

    expect($postalMail->fresh()->method)->toBe(SendMethod::Mail);
});

test('a send can be logged as an email request from the feed composer', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create();
    $material = FeeMaterial::factory()->create();

    Livewire::actingAs($user)->test(FeedComposer::class)
        ->callAction('logSend', data: [
            'player_id' => $player->id,
            'method' => SendMethod::Email->value,
            'date_sent' => now()->toDateString(),
            'fee_material_id' => $material->id,
        ])
        ->assertHasNoActionErrors();

    $postalMail = PostalMail::sole();

    expect($postalMail->method)->toBe(SendMethod::Email)
        ->and($postalMail->feeds()->sole()->meta['send_method'])->toBe('email');
});

test('a send can be logged as an email request from the player page', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create();
    $material = FeeMaterial::factory()->create();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction(TestAction::make('createPostalMail')->table(), data: [
            'method' => SendMethod::Email->value,
            'date_sent' => now()->toDateString(),
            'fee_material_id' => $material->id,
        ])
        ->assertHasNoActionErrors();

    expect(PostalMail::sole()->method)->toBe(SendMethod::Email);
});

test('the feed says an email request was sent', function () {
    $postalMail = PostalMail::factory()->unReturned()->create(['method' => SendMethod::Email]);
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    Livewire::test(UserProfile::class, ['user' => $postalMail->user->slug])
        ->assertSee('sent an email request to')
        ->assertDontSee('sent mail to');
});

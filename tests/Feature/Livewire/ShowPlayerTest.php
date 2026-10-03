<?php

use App\Actions\CreateFeedItem;
use App\Enums\AddressType;
use App\Enums\FailureReason;
use App\Livewire\ShowPlayer;
use App\Models\Address;
use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('guests cannot see a player photo', function () {
    $player = Player::factory()->published()->create();
    $player->media()->create(['url' => 'avatars/test.jpg']);

    $this->get(route('players.show', $player->slug))
        ->assertDontSee('avatars/test.jpg')
        ->assertSeeText('to view photo');
});

test('authenticated users can see a player photo', function () {
    $user = User::factory()->create();
    $player = Player::factory()->published()->create();
    $player->media()->create(['url' => 'avatars/test.jpg']);

    $this->actingAs($user)
        ->get(route('players.show', $player->slug))
        ->assertSee('avatars/test.jpg')
        ->assertDontSeeText('to view photo');
});

test('it can create addresses', function () {
    $user = User::factory()->isSuperAdmin()->create();
    $player = Player::factory()->create();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction('createAddress', data: [
            'address_1' => '2671 Rochester Ave',
            'city' => 'Hamilton',
            'state' => 'Ohio',
            'postal_code' => '45011',
        ])
        ->assertHasNoActionErrors();
});

test('it can create a temporary address with an expiration date', function () {
    $user = User::factory()->isSuperAdmin()->create();
    $player = Player::factory()->create();
    $expiresAt = now()->addMonth()->startOfDay();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction('createAddress', data: [
            'address_1' => '2671 Rochester Ave',
            'city' => 'Hamilton',
            'state' => 'Ohio',
            'postal_code' => '45011',
            'expires_at' => $expiresAt->toDateString(),
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('addresses', [
        'address_1' => '2671 Rochester Ave',
        'expires_at' => $expiresAt,
    ]);
});

test('it can create a fee', function () {
    $user = User::factory()->isSuperAdmin()->create();
    $player = Player::factory()->create();
    $feeMaterial = FeeMaterial::factory()->create();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction('createFee', data: [
            'amount' => 23,
            'fee_material_id' => $feeMaterial->id,
        ])
        ->assertHasNoActionErrors();

    $this->assertTrue($player->fresh()->fees->pluck('amount')->contains(23));
});

it('can edit a postal mail', function () {
    $player = Player::factory()->create();
    $postalMail = PostalMail::factory()->state([
        'signer_id' => $player->signer->id,
        'returned_date' => null,
    ])->create();

    CreateFeedItem::execute($postalMail, $postalMail->comment);

    $returnedDate = now()->subDay();

    Livewire::actingAs($postalMail->user)->test(ShowPlayer::class, ['player' => $postalMail->player])
        ->callAction(TestAction::make('edit')->table($postalMail), ['returned_date' => $returnedDate->format('Y-m-d')])
        ->assertHasNoActionErrors();

    $this->assertEquals($returnedDate?->format('Y-m-d'), $postalMail->fresh()->returned_date?->format('Y-m-d'));
});

it('can add multiple cards to a postal mail in one submission', function () {
    Storage::fake('public');

    $player = Player::factory()->create();
    $postalMail = PostalMail::factory()->state([
        'signer_id' => $player->signer->id,
    ])->create();

    CreateFeedItem::execute($postalMail, $postalMail->comment);

    Livewire::actingAs($postalMail->user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction(TestAction::make('createCard')->table($postalMail), [
            'cards' => [
                [
                    'manufacturer' => 'Donruss',
                    'series' => '1',
                    'year' => 1991,
                    'number' => '371',
                    'url' => [UploadedFile::fake()->image('card-1.jpg')],
                ],
                [
                    'manufacturer' => 'Topps',
                    'series' => '2',
                    'year' => 1992,
                    'number' => '55',
                    'url' => [UploadedFile::fake()->image('card-2.jpg')],
                ],
            ],
        ])
        ->assertHasNoActionErrors();

    expect($postalMail->fresh()->cards)->toHaveCount(2)
        ->and($postalMail->cards()->where('manufacturer', 'Donruss')->exists())->toBeTrue()
        ->and($postalMail->cards()->where('manufacturer', 'Topps')->exists())->toBeTrue();

    $postalMail->cards->each(
        fn ($card) => expect($card->media)->toHaveCount(1)
    );
});

it('can mark a postal mail as failed', function () {
    $player = Player::factory()->create();
    $postalMail = PostalMail::factory()->state([
        'signer_id' => $player->signer->id,
        'returned_date' => null,
    ])->create();

    CreateFeedItem::execute($postalMail, $postalMail->comment);

    Livewire::actingAs($postalMail->user)->test(ShowPlayer::class, ['player' => $postalMail->player])
        ->callAction(TestAction::make('edit')->table($postalMail), [
            'is_failed' => true,
            'failure_reason' => FailureReason::ReturnedUnsigned->value,
        ])
        ->assertHasNoActionErrors();

    $this->assertTrue($postalMail->fresh()->is_failed);
    expect($postalMail->fresh()->failure_reason)->toBe(FailureReason::ReturnedUnsigned);
});

it('requires a reason when marking a postal mail as failed', function () {
    $player = Player::factory()->create();
    $postalMail = PostalMail::factory()->unReturned()->create(['signer_id' => $player->signer->id]);

    CreateFeedItem::execute($postalMail, $postalMail->comment);

    Livewire::actingAs($postalMail->user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction(TestAction::make('edit')->table($postalMail), [
            'is_failed' => true,
        ])
        ->assertHasActionErrors(['failure_reason' => 'required']);
});

test('it can add an email for autograph requests instead of a mailing address', function () {
    $user = User::factory()->isSuperAdmin()->create();
    $player = Player::factory()->create();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction('createAddress', data: [
            'type' => AddressType::Email->value,
            'email' => 'fanmail@example.com',
        ])
        ->assertHasNoActionErrors();

    $address = Address::sole();

    $this->travel(1)->seconds();

    expect($address->type)->toBe(AddressType::Email)
        ->and($address->email)->toBe('fanmail@example.com')
        ->and($address->address_1)->toBeNull()
        ->and($player->emailAddress()?->is($address))->toBeTrue()
        ->and($player->address())->toBeNull();
});

test('an email contact requires a valid email', function () {
    $user = User::factory()->isSuperAdmin()->create();
    $player = Player::factory()->create();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction('createAddress', data: [
            'type' => AddressType::Email->value,
            'email' => 'not-an-email',
        ])
        ->assertHasActionErrors(['email' => 'email']);
});

test('the player page shows both the mailing address and the email contact', function () {
    $user = User::factory()->create();
    $player = Player::factory()->published()->create();
    Address::factory()->published()->create(['signer_id' => $player->signer->id, 'address_1' => '123 Main St']);
    Address::factory()->published()->email()->create(['signer_id' => $player->signer->id, 'email' => 'fanmail@example.com']);

    $this->actingAs($user)
        ->get(route('players.show', $player->slug))
        ->assertSee('123 Main St')
        ->assertSee('fanmail@example.com')
        ->assertSee('mailto:fanmail@example.com', false);
});

test('guests are told a player takes email requests without seeing the email', function () {
    $player = Player::factory()->published()->create();
    Address::factory()->published()->email()->create(['signer_id' => $player->signer->id, 'email' => 'fanmail@example.com']);

    $this->get(route('players.show', $player->slug))
        ->assertSee('This player takes email requests')
        ->assertDontSee('fanmail@example.com');
});

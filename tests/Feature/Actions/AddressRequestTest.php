<?php

use App\Actions\CreateFeedItem;
use App\Actions\RequestAddress;
use App\Enums\AddressRequestReason;
use App\Enums\FailureReason;
use App\Livewire\FeedComposer;
use App\Livewire\ShowPlayer;
use App\Livewire\UserProfile;
use App\Models\Address;
use App\Models\AddressRequest;
use App\Models\Feed;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\User;
use App\Notifications\AddressRequestFulfilled;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake();
});

test('requesting an address posts it to the feed once per user and player', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create();

    $first = RequestAddress::execute($user, $player->signer, AddressRequestReason::MissingAddress, note: 'Anyone?');
    $second = RequestAddress::execute($user, $player->signer, AddressRequestReason::MissingAddress);

    expect($second->id)->toBe($first->id)
        ->and(AddressRequest::count())->toBe(1);

    $feed = $first->feeds()->sole();

    expect($feed->componentName())->toBe('feeds.address-request-card')
        ->and($feed->followable_id)->toBe($user->id)
        ->and($feed->comment)->toBe('Anyone?')
        ->and($feed->meta['player'])->toBe($player->name)
        ->and($feed->meta['reason'])->toBe(AddressRequestReason::MissingAddress->value);
});

test('the address request card is shown in the activity feed', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create();

    RequestAddress::execute($user, $player->signer, AddressRequestReason::ReturnToSender);

    Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->assertSee('is looking for a new address for')
        ->assertSee($player->name)
        ->assertSee('Returned to sender');
});

test('a user can request an address for a player without one', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->assertActionVisible('requestAddress')
        ->callAction('requestAddress', data: ['note' => 'Help!'])
        ->assertHasNoActionErrors()
        ->assertActionHidden('requestAddress')
        ->assertSee("You've requested an address");

    $addressRequest = AddressRequest::sole();

    expect($addressRequest->user_id)->toBe($user->id)
        ->and($addressRequest->signer_id)->toBe($player->signer->id)
        ->and($addressRequest->reason)->toBe(AddressRequestReason::MissingAddress)
        ->and($addressRequest->note)->toBe('Help!')
        ->and($addressRequest->feeds()->exists())->toBeTrue();
});

test('a user can request a new address when the listed one came back return to sender', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create();
    Address::factory()->published()->create(['signer_id' => $player->signer->id]);

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction('requestAddress', data: ['reason' => AddressRequestReason::ReturnToSender->value])
        ->assertHasNoActionErrors();

    expect(AddressRequest::sole()->reason)->toBe(AddressRequestReason::ReturnToSender);
});

test('unpublished users cannot request an address', function () {
    $user = User::factory()->create(['published_at' => null]);
    $player = Player::factory()->create();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->assertActionHidden('requestAddress');
});

test('publishing an address fulfills open requests and notifies the requester', function () {
    Notification::fake();

    $user = User::factory()->create();
    $player = Player::factory()->create();
    $addressRequest = RequestAddress::execute($user, $player->signer, AddressRequestReason::MissingAddress);

    $address = Address::factory()->create(['signer_id' => $player->signer->id]);

    expect($addressRequest->fresh()->isFulfilled())->toBeFalse();

    $address->update(['published_at' => now()->subMinute()]);

    expect($addressRequest->fresh()->isFulfilled())->toBeTrue()
        ->and($addressRequest->feeds()->sole()->meta['fulfilled_at'])->not->toBeNull();

    Notification::assertSentTo($user, AddressRequestFulfilled::class);
});

test('marking a send as return to sender can request a new address', function () {
    $player = Player::factory()->create();
    $postalMail = PostalMail::factory()->unReturned()->create(['signer_id' => $player->signer->id]);
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    Livewire::actingAs($postalMail->user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction(TestAction::make('edit')->table($postalMail), [
            'is_failed' => true,
            'failure_reason' => FailureReason::ReturnToSender->value,
            'request_address' => true,
            'request_address_note' => 'Marked moved',
        ])
        ->assertHasNoActionErrors();

    $postalMail->refresh();
    $addressRequest = AddressRequest::sole();

    expect($postalMail->failure_reason)->toBe(FailureReason::ReturnToSender)
        ->and($addressRequest->postal_mail_id)->toBe($postalMail->id)
        ->and($addressRequest->reason)->toBe(AddressRequestReason::ReturnToSender)
        ->and($addressRequest->note)->toBe('Marked moved');
});

test('other failure reasons do not request an address', function () {
    $player = Player::factory()->create();
    $postalMail = PostalMail::factory()->unReturned()->create(['signer_id' => $player->signer->id]);
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    Livewire::actingAs($postalMail->user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction(TestAction::make('edit')->table($postalMail), [
            'is_failed' => true,
            'failure_reason' => FailureReason::Declined->value,
            'request_address' => true,
        ])
        ->assertHasNoActionErrors();

    expect(AddressRequest::count())->toBe(0);
});

test('the failed return card shows what kind of failure it was', function () {
    $postalMail = PostalMail::factory()->unReturned()->create();
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    $postalMail->fail(FailureReason::ReturnToSender);

    expect(Feed::sole()->meta['failure_reason'])->toBe(FailureReason::ReturnToSender->value);

    Livewire::test(UserProfile::class, ['user' => $postalMail->user->slug])
        ->assertSee('got a failed return from')
        ->assertSee('Return to sender (RTS)');
});

test('un-failing a send clears its failure reason', function () {
    $postalMail = PostalMail::factory()->failed()->create(['failure_reason' => FailureReason::Declined]);

    $postalMail->update(['is_failed' => false]);

    expect($postalMail->fresh()->failure_reason)->toBeNull();
});

test('logging a failed return from the composer records the reason and skips the share prompt', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create();
    $postalMail = PostalMail::factory()->for($user)->unReturned()->create(['signer_id' => $player->signer->id]);
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    Livewire::actingAs($user)->test(FeedComposer::class)
        ->callAction('logReturn', data: [
            'postal_mail_id' => $postalMail->id,
            'returned_date' => now()->toDateString(),
            'is_failed' => true,
            'failure_reason' => FailureReason::ReturnToSender->value,
            'request_address' => true,
        ])
        ->assertHasNoActionErrors()
        ->assertSet('shareOpen', false);

    $postalMail->refresh();

    expect($postalMail->is_failed)->toBeTrue()
        ->and($postalMail->failure_reason)->toBe(FailureReason::ReturnToSender)
        ->and(AddressRequest::where('postal_mail_id', $postalMail->id)->exists())->toBeTrue();
});

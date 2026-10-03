<?php

use App\Enums\FailureReason;
use App\Models\Address;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
});

function reportRts(Player $player, ?User $user = null, ?CarbonInterface $sentAt = null): PostalMail
{
    $postalMail = PostalMail::factory()
        ->for($user ?? User::factory()->create())
        ->unReturned()
        ->create(['signer_id' => $player->signer->id, 'date_sent' => $sentAt ?? now()]);

    $postalMail->fail(FailureReason::ReturnToSender);

    return $postalMail;
}

test('a single rts report does not flag the address', function () {
    $player = Player::factory()->create();
    $address = Address::factory()->published()->create(['signer_id' => $player->signer->id]);

    reportRts($player);

    expect($address->fresh()->rts_flagged_at)->toBeNull();
});

test('two collectors reporting rts flag the address but keep it listed', function () {
    $player = Player::factory()->create();
    $address = Address::factory()->published()->create(['signer_id' => $player->signer->id]);

    reportRts($player);
    reportRts($player);

    $address->refresh();

    expect($address->rts_flagged_at)->not->toBeNull()
        ->and($address->rtsReportCount())->toBe(2)
        ->and($player->address()?->is($address))->toBeTrue();
});

test('repeat reports from the same collector only count once', function () {
    $player = Player::factory()->create();
    $address = Address::factory()->published()->create(['signer_id' => $player->signer->id]);
    $user = User::factory()->create();

    reportRts($player, $user);
    reportRts($player, $user);

    expect($address->fresh()->rts_flagged_at)->toBeNull();
});

test('three collectors reporting rts archive the address', function () {
    $player = Player::factory()->create();
    $address = Address::factory()->published()->create(['signer_id' => $player->signer->id]);

    reportRts($player);
    reportRts($player);
    reportRts($player);

    $this->travel(1)->seconds();

    expect($address->fresh()->isExpired())->toBeTrue()
        ->and($player->address())->toBeNull();
});

test('rts reports on sends made before the address was published are ignored', function () {
    $player = Player::factory()->create();
    $address = Address::factory()->create(['signer_id' => $player->signer->id, 'published_at' => now()->subDay()]);

    reportRts($player, sentAt: now()->subMonth());
    reportRts($player, sentAt: now()->subMonth());

    expect($address->fresh()->rts_flagged_at)->toBeNull();
});

test('other failure reasons do not count as rts reports', function () {
    $player = Player::factory()->create();
    $address = Address::factory()->published()->create(['signer_id' => $player->signer->id]);

    foreach (range(1, 3) as $ignored) {
        PostalMail::factory()->unReturned()->create(['signer_id' => $player->signer->id])->fail(FailureReason::Declined);
    }

    expect($address->fresh()->rts_flagged_at)->toBeNull()
        ->and($address->fresh()->isExpired())->toBeFalse();
});

test('the player page warns when the address has been flagged', function () {
    $player = Player::factory()->published()->create();
    Address::factory()->published()->create(['signer_id' => $player->signer->id]);

    reportRts($player);
    reportRts($player);

    $this->actingAs(User::factory()->create())
        ->get(route('players.show', $player->slug))
        ->assertSee('2 collectors recently had mail to this address returned to sender');
});

<?php

use App\Actions\RequestAddress;
use App\Enums\AddressRequestReason;
use App\Livewire\MostWantedAddresses;
use App\Models\Address;
use App\Models\AddressRequest;
use App\Models\Player;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

test('the most wanted page lists players by open request count', function () {
    $popular = Player::factory()->create(['name' => 'Popular Player']);
    $lessPopular = Player::factory()->create(['name' => 'Less Popular']);
    $answered = Player::factory()->create(['name' => 'Already Answered']);

    AddressRequest::factory()->count(3)->create(['signer_id' => $popular->signer->id]);
    AddressRequest::factory()->create(['signer_id' => $lessPopular->signer->id]);
    AddressRequest::factory()->fulfilled()->create(['signer_id' => $answered->signer->id]);

    $this->get(route('addresses.wanted'))->assertOk();

    $component = Livewire::test(MostWantedAddresses::class);

    $players = $component->instance()->players;

    expect($players->pluck('name')->all())->toBe(['Popular Player', 'Less Popular'])
        ->and($players->first()->open_requests_count)->toBe(3)
        ->and($component->instance()->totalOpenRequests)->toBe(4);
});

test('deceased players are left off the most wanted list', function () {
    $deceased = Player::factory()->create(['deceased_at' => now()->subYear()]);
    AddressRequest::factory()->create(['signer_id' => $deceased->signer->id]);

    expect(Livewire::test(MostWantedAddresses::class)->instance()->players)->toBeEmpty();
});

test('the leaderboard credits whoever answers the most address requests', function () {
    Cache::flush();
    $helper = User::factory()->create();
    $player = Player::factory()->create();

    RequestAddress::execute(User::factory()->create(), $player->signer, AddressRequestReason::MissingAddress);
    RequestAddress::execute(User::factory()->create(), $player->signer, AddressRequestReason::MissingAddress);

    $address = Address::factory()->published()->create(['signer_id' => $player->signer->id, 'user_id' => $helper->id]);

    expect(AddressRequest::where('fulfilled_by_address_id', $address->id)->count())->toBe(2);

    $result = app(LeaderboardService::class)->compute();

    expect($result['requests_answered']['user']->is($helper))->toBeTrue()
        ->and($result['requests_answered']['count'])->toBe(2);
});

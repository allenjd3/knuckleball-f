<?php

use App\Models\Address;
use App\Models\Card;
use App\Models\CardSet;
use App\Models\Pack;
use App\Models\PostalMail;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::forget('leaderboard');
});

test('it crowns the user with the most sends', function () {
    $leader = User::factory()->create();
    $other = User::factory()->create();

    PostalMail::factory()->count(3)->create(['user_id' => $leader->id, 'date_sent' => now()]);
    PostalMail::factory()->count(1)->create(['user_id' => $other->id, 'date_sent' => now()]);

    $result = app(LeaderboardService::class)->compute();

    expect($result['sends']['user']->is($leader))->toBeTrue()
        ->and($result['sends']['count'])->toBe(3);
});

test('it crowns the user with the most returns, separately from sends', function () {
    $sender = User::factory()->create();
    $returner = User::factory()->create();

    PostalMail::factory()->count(5)->create(['user_id' => $sender->id, 'date_sent' => now(), 'returned_date' => null]);
    PostalMail::factory()->count(2)->create(['user_id' => $returner->id, 'date_sent' => now(), 'returned_date' => now()]);

    $result = app(LeaderboardService::class)->compute();

    expect($result['sends']['user']->is($sender))->toBeTrue()
        ->and($result['returns']['user']->is($returner))->toBeTrue();
});

test('it crowns the user with the most addresses added', function () {
    $leader = User::factory()->create();
    Address::factory()->count(4)->create(['user_id' => $leader->id]);
    Address::factory()->count(1)->create(['user_id' => User::factory()->create()->id]);

    $result = app(LeaderboardService::class)->compute();

    expect($result['addresses']['user']->is($leader))->toBeTrue()
        ->and($result['addresses']['count'])->toBe(4);
});

test('it crowns the user with the most card images added', function () {
    $leader = User::factory()->create();
    Card::factory()->count(6)->create(['user_id' => $leader->id]);
    Card::factory()->count(2)->create(['user_id' => User::factory()->create()->id]);

    $result = app(LeaderboardService::class)->compute();

    expect($result['cards']['user']->is($leader))->toBeTrue()
        ->and($result['cards']['count'])->toBe(6);
});

test('it crowns the user with the most packs and sets', function () {
    $packLeader = User::factory()->create();
    $setLeader = User::factory()->create();

    Pack::create(['user_id' => $packLeader->id, 'name' => 'Pack One', 'is_public' => true]);
    Pack::create(['user_id' => $packLeader->id, 'name' => 'Pack Two', 'is_public' => true]);
    Pack::create(['user_id' => User::factory()->create()->id, 'name' => 'Pack Three', 'is_public' => true]);

    CardSet::create(['user_id' => $setLeader->id, 'name' => 'Set One', 'is_public' => true]);
    CardSet::create(['user_id' => $setLeader->id, 'name' => 'Set Two', 'is_public' => true]);
    CardSet::create(['user_id' => $setLeader->id, 'name' => 'Set Three', 'is_public' => true]);

    $result = app(LeaderboardService::class)->compute();

    expect($result['packs']['user']->is($packLeader))->toBeTrue()
        ->and($result['packs']['count'])->toBe(2)
        ->and($result['sets']['user']->is($setLeader))->toBeTrue()
        ->and($result['sets']['count'])->toBe(3);
});

test('a category is null when there is no data at all', function () {
    $result = app(LeaderboardService::class)->compute();

    expect($result['packs'])->toBeNull()
        ->and($result['sets'])->toBeNull();
});

<?php

use App\Models\PostalMail;
use App\Models\User;
use App\Services\UserStatsService;
use Illuminate\Support\Facades\Cache;

it('returns string dates, not Carbon objects, so they survive cache serialization', function () {
    $user = User::factory()->create();
    PostalMail::factory()->returned()->create(['user_id' => $user->id]);

    $stats = app(UserStatsService::class)->compute($user);

    expect($stats['first_send'])->toBeString();
    expect($stats['most_recent_return'])->toBeString();
});

it('caches the result and returns the same values on subsequent calls', function () {
    $user = User::factory()->create();
    PostalMail::factory()->returned()->create(['user_id' => $user->id]);

    $service = app(UserStatsService::class);
    $first = $service->compute($user);
    $second = $service->compute($user);

    expect($second)->toBe($first);
    expect(Cache::has("user_stats_{$user->id}"))->toBeTrue();
});

it('returns null dates when the user has no sends', function () {
    $user = User::factory()->create();

    $stats = app(UserStatsService::class)->compute($user);

    expect($stats['first_send'])->toBeNull();
    expect($stats['most_recent_return'])->toBeNull();
});

it('renders the profile page without error when stats contain date strings', function () {
    $user = User::factory()->create();
    PostalMail::factory()->returned()->create(['user_id' => $user->id]);

    $this->get(route('users.profile', $user))->assertOk();
});

it('renders the profile page without error when stats are served from cache with date strings', function () {
    $user = User::factory()->create();
    PostalMail::factory()->returned()->create(['user_id' => $user->id]);

    // Prime the cache
    app(UserStatsService::class)->compute($user);

    // Second request hits the cache
    $this->get(route('users.profile', $user))->assertOk();
});

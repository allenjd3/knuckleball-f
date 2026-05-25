<?php

use App\Jobs\DispatchWatchlistAlerts;
use App\Models\Event;
use App\Models\Player;
use App\Models\User;
use App\Notifications\CardShowAlert;
use App\Notifications\WatchlistSigningAlert;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Notification;

// NYC coords (used as event location)
const EVENT_LAT = 40.7128;
const EVENT_LNG = -74.0060;

// Close-by coords (~5 miles from NYC)
const NEAR_LAT = 40.7580;
const NEAR_LNG = -73.9855;

// Far-away coords (Los Angeles, ~2450 miles)
const FAR_LAT = 34.0522;
const FAR_LNG = -118.2437;

beforeEach(function () {
    Notification::fake();
});

test('it notifies all watchers of a mail-in signing regardless of location', function () {
    $player = Player::factory()->create();

    $watcher = User::factory()->create(['zip_code' => null]);
    $watcher->watchlist()->attach($player);

    $event = Event::factory()->mailIn($player)->approved()->create();

    DispatchWatchlistAlerts::dispatch($event->id);

    Notification::assertSentTo($watcher, WatchlistSigningAlert::class);
});

test('it notifies watchers within radius for in-person signing', function () {
    $this->mock(GeocodingService::class, fn ($mock) => $mock->shouldReceive('geocodeZip')
        ->andReturn(['latitude' => NEAR_LAT, 'longitude' => NEAR_LNG])
    );

    $player = Player::factory()->create();
    $watcher = User::factory()->create(['zip_code' => '10001', 'radius' => 50]);
    $watcher->watchlist()->attach($player);

    $event = Event::factory()
        ->playerSigning($player)
        ->approved()
        ->withLocation(EVENT_LAT, EVENT_LNG)
        ->create();

    DispatchWatchlistAlerts::dispatch($event->id);

    Notification::assertSentTo($watcher, WatchlistSigningAlert::class);
});

test('it does not notify watchers outside radius for in-person signing', function () {
    $this->mock(GeocodingService::class, fn ($mock) => $mock->shouldReceive('geocodeZip')
        ->andReturn(['latitude' => FAR_LAT, 'longitude' => FAR_LNG])
    );

    $player = Player::factory()->create();
    $watcher = User::factory()->create(['zip_code' => '90001', 'radius' => 50]);
    $watcher->watchlist()->attach($player);

    $event = Event::factory()
        ->playerSigning($player)
        ->approved()
        ->withLocation(EVENT_LAT, EVENT_LNG)
        ->create();

    DispatchWatchlistAlerts::dispatch($event->id);

    Notification::assertNotSentTo($watcher, WatchlistSigningAlert::class);
});

test('it notifies card-show-alert users within radius for a card show', function () {
    $this->mock(GeocodingService::class, fn ($mock) => $mock->shouldReceive('geocodeZip')
        ->andReturn(['latitude' => NEAR_LAT, 'longitude' => NEAR_LNG])
    );

    $user = User::factory()->create([
        'card_show_alerts' => true,
        'zip_code' => '10001',
        'radius' => 50,
    ]);

    $event = Event::factory()
        ->cardShow()
        ->approved()
        ->withLocation(EVENT_LAT, EVENT_LNG)
        ->create();

    DispatchWatchlistAlerts::dispatch($event->id);

    Notification::assertSentTo($user, CardShowAlert::class);
});

test('it does not notify card-show-alert users outside radius', function () {
    $this->mock(GeocodingService::class, fn ($mock) => $mock->shouldReceive('geocodeZip')
        ->andReturn(['latitude' => FAR_LAT, 'longitude' => FAR_LNG])
    );

    $user = User::factory()->create([
        'card_show_alerts' => true,
        'zip_code' => '90001',
        'radius' => 50,
    ]);

    $event = Event::factory()
        ->cardShow()
        ->approved()
        ->withLocation(EVENT_LAT, EVENT_LNG)
        ->create();

    DispatchWatchlistAlerts::dispatch($event->id);

    Notification::assertNotSentTo($user, CardShowAlert::class);
});

test('it does not notify users who have card_show_alerts disabled', function () {
    $this->mock(GeocodingService::class, fn ($mock) => $mock->shouldReceive('geocodeZip')
        ->andReturn(['latitude' => NEAR_LAT, 'longitude' => NEAR_LNG])
    );

    $user = User::factory()->create([
        'card_show_alerts' => false,
        'zip_code' => '10001',
        'radius' => 50,
    ]);

    $event = Event::factory()
        ->cardShow()
        ->approved()
        ->withLocation(EVENT_LAT, EVENT_LNG)
        ->create();

    DispatchWatchlistAlerts::dispatch($event->id);

    Notification::assertNotSentTo($user, CardShowAlert::class);
});

test('it handles a missing event gracefully', function () {
    DispatchWatchlistAlerts::dispatch(99999);

    Notification::assertNothingSent();
});

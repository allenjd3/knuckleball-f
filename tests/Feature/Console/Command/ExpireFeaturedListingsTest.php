<?php

use App\Models\Event;
use App\Models\FeaturedListing;
use App\Notifications\FeaturedListingExpired;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

test('it un-features an event and notifies the owner when a listing expires', function () {
    $event = Event::factory()->approved()->featured()->create();
    FeaturedListing::factory()->expired()->for($event)->create();

    $this->artisan('featured:expire')->assertSuccessful();

    expect($event->fresh()->is_featured)->toBeFalse();
    Notification::assertSentTo($event->user, FeaturedListingExpired::class);
});

test('it does not un-feature the event if a subscription listing is still active', function () {
    $event = Event::factory()->approved()->featured()->create();

    FeaturedListing::factory()->expired()->for($event)->create();
    FeaturedListing::factory()->subscription()->for($event)->create();

    $this->artisan('featured:expire')->assertSuccessful();

    expect($event->fresh()->is_featured)->toBeTrue();
    Notification::assertNotSentTo($event->user, FeaturedListingExpired::class);
});

test('it sends only one notification when multiple one-time listings expire for the same event', function () {
    $event = Event::factory()->approved()->featured()->create();

    FeaturedListing::factory()->expired()->for($event)->create();
    FeaturedListing::factory()->expired()->for($event)->create();

    $this->artisan('featured:expire')->assertSuccessful();

    Notification::assertSentToTimes($event->user, FeaturedListingExpired::class, 1);
    expect($event->fresh()->is_featured)->toBeFalse();
});

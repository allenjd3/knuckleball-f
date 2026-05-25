<?php

use App\Models\Event;
use App\Models\FeaturedListing;

test('featuredListing returns an active one-time listing', function () {
    $event = Event::factory()->approved()->create();
    $listing = FeaturedListing::factory()->for($event)->create();

    expect($event->featuredListing->id)->toBe($listing->id);
});

test('featuredListing returns a subscription listing with null expires_at', function () {
    $event = Event::factory()->approved()->create();
    $listing = FeaturedListing::factory()->subscription()->for($event)->create();

    expect($event->featuredListing->id)->toBe($listing->id);
});

test('featuredListing returns null when only an expired listing exists', function () {
    $event = Event::factory()->approved()->create();
    FeaturedListing::factory()->expired()->for($event)->create();

    expect($event->fresh()->featuredListing)->toBeNull();
});

test('featuredListing returns null when no listing exists', function () {
    $event = Event::factory()->approved()->create();

    expect($event->featuredListing)->toBeNull();
});

test('event type player_signing can be stored', function () {
    $event = Event::factory()->create(['type' => 'player_signing']);
    $this->assertDatabaseHas('events', ['id' => $event->id, 'type' => 'player_signing']);
});

test('event type card_show can be stored', function () {
    $event = Event::factory()->create(['type' => 'card_show']);
    $this->assertDatabaseHas('events', ['id' => $event->id, 'type' => 'card_show']);
});

test('event type comic_con can be stored', function () {
    $event = Event::factory()->create(['type' => 'comic_con']);
    $this->assertDatabaseHas('events', ['id' => $event->id, 'type' => 'comic_con']);
});

test('event type memorabilia_show can be stored', function () {
    $event = Event::factory()->create(['type' => 'memorabilia_show']);
    $this->assertDatabaseHas('events', ['id' => $event->id, 'type' => 'memorabilia_show']);
});

test('isMailIn returns true for mail-in player signings', function () {
    $event = Event::factory()->mailIn()->create();
    expect($event->isMailIn())->toBeTrue();
});

test('isMailIn returns false for in-person signings', function () {
    $event = Event::factory()->playerSigning()->create();
    expect($event->isMailIn())->toBeFalse();
});

test('scopeApproved filters to approved events only', function () {
    Event::factory()->create(['status' => 'pending']);
    Event::factory()->approved()->create();
    Event::factory()->create(['status' => 'rejected']);

    expect(Event::approved()->count())->toBe(1);
});

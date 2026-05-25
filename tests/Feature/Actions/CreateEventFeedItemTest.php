<?php

use App\Actions\CreateEventFeedItem;
use App\Jobs\DispatchWatchlistAlerts;
use App\Models\Event;
use App\Models\Feed;
use App\Models\User;
use App\Notifications\EventApprovedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

test('it creates a feed item for the event', function () {
    $event = Event::factory()->approved()->create();

    CreateEventFeedItem::execute($event);

    $this->assertDatabaseHas('feeds', [
        'feedable_type' => Event::class,
        'feedable_id' => $event->id,
        'followable_id' => $event->user_id,
    ]);
});

test('it notifies the event submitter', function () {
    Notification::fake();

    $event = Event::factory()->approved()->create();

    CreateEventFeedItem::execute($event);

    Notification::assertSentTo($event->user, EventApprovedNotification::class);
});

test('it dispatches DispatchWatchlistAlerts job', function () {
    Queue::fake();

    $event = Event::factory()->approved()->create();

    CreateEventFeedItem::execute($event);

    Queue::assertPushed(DispatchWatchlistAlerts::class, fn ($job) => $job->eventId === $event->id);
});

test('it attaches to a recent digest from the same user', function () {
    $user = User::factory()->create();
    $event = Event::factory()->approved()->for($user)->create();

    $digest = Feed::create([
        'followable_id' => $user->id,
        'feedable_type' => Event::class,
        'feedable_id' => $event->id,
        'comment' => '',
        'meta' => ['event_ids' => [$event->id]],
    ]);

    $event2 = Event::factory()->approved()->for($user)->create();

    CreateEventFeedItem::execute($event2);

    expect(Feed::count())->toBe(1);
    expect($digest->fresh()->meta['event_ids'])->toContain($event2->id);
});

test('it does not attach to a digest belonging to a different user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $event1 = Event::factory()->approved()->for($user1)->create();

    Feed::create([
        'followable_id' => $user1->id,
        'feedable_type' => Event::class,
        'feedable_id' => $event1->id,
        'comment' => '',
        'meta' => ['event_ids' => [$event1->id]],
    ]);

    $event2 = Event::factory()->approved()->for($user2)->create();

    CreateEventFeedItem::execute($event2);

    expect(Feed::count())->toBe(2);
    $this->assertDatabaseHas('feeds', ['followable_id' => $user2->id]);
});

test('it creates a new feed if the digest is older than 60 minutes', function () {
    $user = User::factory()->create();
    $event1 = Event::factory()->approved()->for($user)->create();

    Feed::create([
        'followable_id' => $user->id,
        'feedable_type' => Event::class,
        'feedable_id' => $event1->id,
        'comment' => '',
        'meta' => ['event_ids' => [$event1->id]],
        'created_at' => now()->subMinutes(61),
    ]);

    $event2 = Event::factory()->approved()->for($user)->create();

    CreateEventFeedItem::execute($event2);

    expect(Feed::count())->toBe(2);
});

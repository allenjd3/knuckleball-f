<?php

use App\Actions\CreateFeedItem;
use App\Models\Feed;
use App\Models\PostalMail;

function staleFeedFor(PostalMail $postalMail): Feed
{
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    $feed = Feed::query()
        ->where('feedable_type', PostalMail::class)
        ->where('feedable_id', $postalMail->id)
        ->firstOrFail();

    $feed->meta = collect($feed->meta)->except('is_failed')->all();
    $feed->saveQuietly();

    return $feed;
}

it('flags feeds for failed postal mail', function () {
    $failedFeed = staleFeedFor(PostalMail::factory()->failed()->create());
    $returnedFeed = staleFeedFor(PostalMail::factory()->returned()->create());

    $this->artisan('operation:backfill-failed-feed-meta')
        ->expectsOutput('Flagged 1 failed feed item.')
        ->assertSuccessful();

    expect(data_get($failedFeed->fresh()->meta, 'is_failed'))->toBeTrue()
        ->and($failedFeed->fresh()->componentName())->toBe('feeds.failed-return-card')
        ->and(data_get($returnedFeed->fresh()->meta, 'is_failed'))->toBeNull();
});

it('does nothing when run a second time', function () {
    staleFeedFor(PostalMail::factory()->failed()->create());

    $this->artisan('operation:backfill-failed-feed-meta')->assertSuccessful();

    $this->artisan('operation:backfill-failed-feed-meta')
        ->expectsOutput('Flagged 0 failed feed items.')
        ->assertSuccessful();
});

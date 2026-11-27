<?php

use App\Actions\CreateFeedItem;
use App\Jobs\GenerateReturnCard;
use App\Models\Feed;
use App\Models\PostalMail;
use Illuminate\Support\Facades\Queue;

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

beforeEach(function () {
    Queue::fake();
});

it('flags feeds for failed postal mail', function () {
    $failedFeed = staleFeedFor(PostalMail::factory()->failed()->create());
    $returnedFeed = staleFeedFor(PostalMail::factory()->returned()->create());

    $this->artisan('operation:backfill-failed-feed-meta')
        ->expectsOutput('Flagged 1 failed feed item and queued 0 share cards to rebuild.')
        ->assertSuccessful();

    expect(data_get($failedFeed->fresh()->meta, 'is_failed'))->toBeTrue()
        ->and($failedFeed->fresh()->componentName())->toBe('feeds.failed-return-card')
        ->and(data_get($returnedFeed->fresh()->meta, 'is_failed'))->toBeNull();

    Queue::assertNotPushed(GenerateReturnCard::class);
});

it('rebuilds share cards for failed postal mail that has a returned date', function () {
    $postalMail = PostalMail::factory()->failed()->create(['returned_date' => now()->subDay()]);
    staleFeedFor($postalMail);
    Queue::fake();

    $this->artisan('operation:backfill-failed-feed-meta')
        ->expectsOutput('Flagged 1 failed feed item and queued 1 share card to rebuild.')
        ->assertSuccessful();

    Queue::assertPushed(GenerateReturnCard::class, fn (GenerateReturnCard $job): bool => $job->postalMailId === $postalMail->id);
});

it('does nothing when run a second time', function () {
    staleFeedFor(PostalMail::factory()->failed()->create());

    $this->artisan('operation:backfill-failed-feed-meta')->assertSuccessful();

    $this->artisan('operation:backfill-failed-feed-meta')
        ->expectsOutput('Flagged 0 failed feed items and queued 0 share cards to rebuild.')
        ->assertSuccessful();
});

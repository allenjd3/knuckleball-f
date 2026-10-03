<?php

use App\Actions\CreateFeedItem;
use App\Jobs\GenerateReturnCard;
use App\Livewire\UserProfile;
use App\Models\Feed;
use App\Models\PostalMail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake();
});

test('marking a return as failed switches its feed item to the failed return card', function () {
    $postalMail = PostalMail::factory()->returned()->create();
    $feed = CreateFeedItem::execute($postalMail, $postalMail->comment);

    expect($feed->componentName())->toBe('feeds.celebration-card');

    $postalMail->fail();

    $feed->refresh();

    expect($feed->meta['is_failed'])->toBeTrue()
        ->and($feed->componentName())->toBe('feeds.failed-return-card');
});

test('a return logged as failed uses the failed return card even without a returned date', function () {
    $postalMail = PostalMail::factory()->failed()->create();
    $feed = CreateFeedItem::execute($postalMail, $postalMail->comment);

    expect($feed->componentName())->toBe('feeds.failed-return-card');
});

test('a successful return is not flagged as failed', function () {
    $postalMail = PostalMail::factory()->returned()->create();
    $feed = CreateFeedItem::execute($postalMail, $postalMail->comment);

    expect($feed->meta['is_failed'])->toBeFalse()
        ->and($feed->componentName())->toBe('feeds.celebration-card');
});

test('the activity feed says the user got a failed return', function () {
    $postalMail = PostalMail::factory()->returned()->create(['is_failed' => true]);
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    Livewire::test(UserProfile::class, ['user' => $postalMail->user->slug])
        ->assertSee('got a failed return from')
        ->assertDontSee('got a return from');
});

test('the return page describes a failed return', function () {
    $postalMail = PostalMail::factory()->returned()->create(['is_failed' => true]);

    $this->get(route('returns.show', $postalMail))
        ->assertOk()
        ->assertSee('Got a failed return')
        ->assertDontSee('Got it back!');
});

test('the backfill migration flags existing feed items for failed returns', function () {
    $failed = PostalMail::factory()->returned()->create(['is_failed' => true]);
    $failedFeed = Feed::factory()->create([
        'feedable_id' => $failed->id,
        'feedable_type' => PostalMail::class,
        'followable_id' => $failed->user_id,
        'meta' => ['date_returned' => now()->toDateString()],
    ]);

    $returned = PostalMail::factory()->returned()->create();
    $returnedFeed = Feed::factory()->create([
        'feedable_id' => $returned->id,
        'feedable_type' => PostalMail::class,
        'followable_id' => $returned->user_id,
        'meta' => ['date_returned' => now()->toDateString()],
    ]);

    (require database_path('migrations/2026_10_03_000000_backfill_is_failed_on_postal_mail_feeds.php'))->up();

    expect($failedFeed->fresh()->componentName())->toBe('feeds.failed-return-card')
        ->and($returnedFeed->fresh()->componentName())->toBe('feeds.celebration-card');
});

test('marking a returned mail as failed regenerates its share card', function () {
    Queue::fake();
    $postalMail = PostalMail::factory()->returned()->create();

    $postalMail->fail();

    Queue::assertPushed(GenerateReturnCard::class, fn (GenerateReturnCard $job) => $job->postalMailId === $postalMail->id);
});

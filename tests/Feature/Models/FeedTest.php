<?php

use App\Actions\CreateFeedItem;
use App\Models\Comment;
use App\Models\Feed;
use App\Models\PostalMail;

function feedFor(PostalMail $postalMail): Feed
{
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    return Feed::query()
        ->where('feedable_type', PostalMail::class)
        ->where('feedable_id', $postalMail->id)
        ->firstOrFail();
}

it('renders a failed return card for failed postal mail', function (array $state) {
    $feed = feedFor(PostalMail::factory()->failed()->create($state));

    expect($feed->componentName())->toBe('feeds.failed-return-card');
})->with([
    'without a returned date' => [[]],
    'with a returned date' => [['returned_date' => now()->subDay()]],
]);

it('renders a celebration card for a successful return', function () {
    $feed = feedFor(PostalMail::factory()->returned()->create());

    expect($feed->componentName())->toBe('feeds.celebration-card');
});

it('renders a send card for pending postal mail', function () {
    $feed = feedFor(PostalMail::factory()->unReturned()->create());

    expect($feed->componentName())->toBe('feeds.send-card');
});

it('shows the failed return card in the feed', function () {
    $feed = feedFor(PostalMail::factory()->failed()->create());

    $this->blade('<x-dynamic-component :component="$feed->componentName()" :$feed />', ['feed' => $feed])
        ->assertSee('got a failed return from')
        ->assertSee('Failed')
        ->assertDontSee('got a return from');
});

it('shows card photos on a failed return card', function () {
    $feed = Feed::factory()->create([
        'feedable_type' => PostalMail::class,
        'feedable_id' => PostalMail::factory()->failed()->create()->id,
        'meta' => [
            'user' => 'Collector',
            'player' => 'Some Player',
            'date_sent' => now()->subWeek(),
            'is_failed' => true,
            'card_photos' => ['cards/unsigned-card.jpg'],
        ],
    ]);

    $this->blade('<x-dynamic-component :component="$feed->componentName()" :$feed />', ['feed' => $feed])
        ->assertSee('cards/unsigned-card.jpg');
});

it('leaves the date off a failed return card with no sent date', function () {
    $feed = Feed::factory()->create([
        'feedable_type' => PostalMail::class,
        'feedable_id' => PostalMail::factory()->failed()->create()->id,
        'meta' => ['user' => 'Collector', 'player' => 'Some Player', 'is_failed' => true],
    ]);

    $this->blade('<x-dynamic-component :component="$feed->componentName()" :$feed />', ['feed' => $feed])
        ->assertSee('got a failed return from')
        ->assertDontSee(now()->format('M j, Y'));
});

it('scopes feeds to pending, returned and failed sends', function () {
    $pending = feedFor(PostalMail::factory()->unReturned()->create());
    $returned = feedFor(PostalMail::factory()->returned()->create());
    $failed = feedFor(PostalMail::factory()->failed()->create());
    $failedWithReturnDate = feedFor(PostalMail::factory()->failed()->create(['returned_date' => now()->subDay()]));

    expect(Feed::query()->pendingSends()->pluck('id')->all())->toEqualCanonicalizing([$pending->id])
        ->and(Feed::query()->returnedSends()->pluck('id')->all())->toEqualCanonicalizing([$returned->id])
        ->and(Feed::query()->failedSends()->pluck('id')->all())->toEqualCanonicalizing([$failed->id, $failedWithReturnDate->id]);
});

it('leaves non postal mail feeds out of the send scopes', function () {
    Feed::factory()->create(['feedable_type' => Comment::class, 'feedable_id' => 1]);

    expect(Feed::query()->pendingSends()->count())->toBe(0)
        ->and(Feed::query()->returnedSends()->count())->toBe(0)
        ->and(Feed::query()->failedSends()->count())->toBe(0);
});

<?php

use App\Actions\CreateFeedItem;
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

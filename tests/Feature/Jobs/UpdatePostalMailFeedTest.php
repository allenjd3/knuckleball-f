<?php

use App\Actions\CreateFeedItem;
use App\Jobs\UpdatePostalMailFeed;
use App\Models\Feed;
use App\Models\PostalMail;

test('it updates the postal mail feed', function () {
    $badPostalMail = PostalMail::factory()->state(['returned_date' => null])->create();
    CreateFeedItem::execute($badPostalMail, $badPostalMail->comment);

    $badPostalMail->update(['returned_date' => now()->subDay()]);

    UpdatePostalMailFeed::dispatch();

    $feed = Feed::first();
    $this->assertEquals(json_decode(json_encode($badPostalMail->fresh()->generateMeta()), true), $feed->meta);
    $this->assertNotNull(Feed::first()->meta['date_returned']);
});

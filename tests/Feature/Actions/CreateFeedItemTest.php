<?php

use App\Actions\CreateFeedItem;
use App\Models\Feed;
use App\Models\PostalMail;

test('it creates a feed item from a postal mail', function () {
    $postalMail = PostalMail::factory()->create();

    CreateFeedItem::execute(feedItem: $postalMail, comment: $postalMail->comment);

    $this->assertEquals(collect($postalMail->generateMeta())->map(fn ($mail) => json_decode(json_encode($mail)))->toArray(), Feed::latest()->first()->meta);
});

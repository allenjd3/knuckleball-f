<?php

use App\Models\Feed;

it('deletes feed items', function () {
    $feed = Feed::factory()->postalMail()->create();
    $postalMail = $feed->feedable;

    $this->assertCount(1, Feed::all());
    $postalMail->delete();

    $this->assertCount(0, Feed::all());
});

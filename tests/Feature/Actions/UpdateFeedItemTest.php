<?php

use App\Actions\CreateFeedItem;
use App\Models\PostalMail;
use App\Models\Signer;

test('it updates a feed item', function () {
    $postalMail = PostalMail::factory()->unReturned()->create();

    CreateFeedItem::execute($postalMail, $postalMail->comment);
    $postalMail->update(['returned_date' => now()->subDay()]);

    $this->assertEquals(json_decode(json_encode($postalMail->fresh()->returned_date), true), $postalMail->feeds()->first()->meta['date_returned']);
});

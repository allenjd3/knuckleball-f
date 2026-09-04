<?php

use App\Actions\CreateFeedItem;
use App\Models\Feed;
use App\Models\InPersonAutograph;
use App\Models\Player;
use App\Models\PostalMail;

test('it updates a feed item', function () {
    $postalMail = PostalMail::factory()->unReturned()->create();

    CreateFeedItem::execute($postalMail, $postalMail->comment);
    $postalMail->update(['returned_date' => now()->subDay()]);

    $this->assertEquals(json_decode(json_encode($postalMail->fresh()->returned_date), true), $postalMail->feeds()->first()->meta['date_returned']);
});

test('updating one feedable type does not overwrite another feedable type feed even when their ids collide', function () {
    $player = Player::factory()->create();

    // PostalMail and InPersonAutograph auto-increment independently, so
    // sharing id=1 here is the normal case, not a contrived one.
    $mail = PostalMail::factory()->create(['signer_id' => $player->signer->id, 'returned_date' => now()]);
    CreateFeedItem::execute($mail, 'ttm comment');

    $autograph = InPersonAutograph::factory()->create(['signer_id' => $player->signer->id]);
    expect($autograph->id)->toBe($mail->id, 'this test only proves anything if the ids actually collide');
    CreateFeedItem::execute($autograph, 'in person comment');

    $autograph->update(['location' => 'Somewhere new']);

    $ttmFeed = Feed::where('feedable_type', PostalMail::class)->where('feedable_id', $mail->id)->first();

    expect($ttmFeed->comment)->toBe('ttm comment')
        ->and($ttmFeed->componentName())->toBe('feeds.celebration-card');
});

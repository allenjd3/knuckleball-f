<?php

use App\Actions\CreateFeedItem;
use App\Models\NoCard;
use App\Models\PostalMail;

test('it includes a default card if it doesnt have one', function () {
    PostalMail::factory()->create();

    expect(PostalMail::first()->card)->toBeInstanceOf(NoCard::class);
});

it('can be marked as failed', function () {
    $postalMail = PostalMail::factory()->create();
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    $this->assertFalse($postalMail->is_failed);

    $postalMail->fail();

    $this->assertTrue($postalMail->is_failed);
});

<?php

use App\Models\NoCard;
use App\Models\PostalMail;

test('it includes a default card if it doesnt have one', function () {
    PostalMail::factory()->create();

    expect(PostalMail::first()->card)->toBeInstanceOf(NoCard::class);
});

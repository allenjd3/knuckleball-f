<?php

use App\Actions\GetMentions;
use App\Models\User;

test('it gets the mentions and attaches them to comments', function () {
    $user = User::factory()->create();

    $html = "I am mentioning @{$user->handle}";

    $mentioned = GetMentions::handle($html);

    expect($mentioned->first()->id)->toBe($user->id);
});

test('it matches all handles in the string', function () {
    [$user1, $user2] = User::factory(2)->create();
    $html = "I am mentioning @{$user1->handle} and @{$user2->handle}";

    $mentioned = GetMentions::handle($html);

    expect($mentioned->count())->toBe(2);
    expect($mentioned->first()->id)->toBe($user1->id);
    expect($mentioned->last()->id)->toBe($user2->id);
});
<?php

use App\Actions\ReplaceMentions;
use App\Models\User;

test("it replaces mentions", function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $html = "I'm mentioning @{$user->handle} and @{$user2->handle}";
    $mentions = collect([$user, $user2]);

    expect(ReplaceMentions::handle($html, $mentions))->toBe("I'm mentioning <a href=\"{$user->path()}\">@{$user->handle}</a> and <a href=\"{$user2->path()}\">@{$user2->handle}</a>");
});
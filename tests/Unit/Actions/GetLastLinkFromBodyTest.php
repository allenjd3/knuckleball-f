<?php

use App\Actions\GetLastLinkFromBody;

test('it gets the last link from the body of html', function () {
    $exampleHtml = 'This is an example. It has multiple <a href="https://weather.com">Links</a>. Links of various <a href="https://jamesdallen.me">kinds</a>.';

    expect(GetLastLinkFromBody::handle($exampleHtml))->toBe('https://jamesdallen.me');
});

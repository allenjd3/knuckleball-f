<?php

use App\Actions\ReplacePastedLinks;

test('it replaces links in a string', function () {
    $html = 'this is a string with a link https://weather.com';

    expect(ReplacePastedLinks::handle($html))->toBe('this is a string with a link <a href="https://weather.com">https://weather.com</a>');
});

test('it does not replace links that are already replaced', function () {
    $html = 'this is a string with a link <a href="https://weather.com">https://weather.com</a>';

    expect(ReplacePastedLinks::handle($html))->toBe('this is a string with a link <a href="https://weather.com">https://weather.com</a>');
});

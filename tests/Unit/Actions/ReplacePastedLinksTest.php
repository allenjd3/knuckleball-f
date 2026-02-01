<?php

use App\Actions\ReplacePastedLinks;

test('it replaces links in a string', function () {
    $html = '<p>this is a string with a link https://weather.com</p>';

    expect(ReplacePastedLinks::handle($html))->toBe('<p>this is a string with a link <a href="https://weather.com">https://weather.com</a></p>');
});

test('it does not replace links that are already replaced', function () {
    $html = '<p>this is a string with a link <a href="https://weather.com">https://weather.com</a></p>';

    expect(ReplacePastedLinks::handle($html))->toBe('<p>this is a string with a link <a href="https://weather.com">https://weather.com</a></p>');
});

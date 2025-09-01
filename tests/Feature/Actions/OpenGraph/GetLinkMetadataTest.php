<?php

use App\Actions\OpenGraph\GetLinkMetadata;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

test('it queries the metadata', function () {
    Storage::fake();
    $fakeMeta = '<meta property="og:title" content="Example Title" />';
    $fakeMeta .= '<meta property="og:description" content="Example Description" />';
    $fakeMeta .= '<meta property="og:image" content="https://example.com/someimage.png"';
    Http::fake([
        'https://jamesdallen.me' => Http::response($fakeMeta, 200),
        '*' => Http::response('fake', 200),
    ]);

    $getLinkMetadata = new GetLinkMetadata;
    $getLinkMetadata(
        'https://jamesdallen.me',
        fn ($val) => expect($val)->toBe([
            'url' => 'https://jamesdallen.me',
            'image' => 'someimage.png',
            'title' => 'Example Title',
            'description' => 'Example Description',
        ]),
    );
});

<?php

use App\Actions\OpenGraph\MoveImageToStorage;
use Illuminate\Support\Facades\Storage;

test('it moves the processed image to the final disk', function () {
    Storage::fake();

    $moveImageToStorage = new MoveImageToStorage;
    $moveImageToStorage(
        [
            'url' => 'https://jamesdallen.me',
            'image' => 'someimage.png',
            'title' => 'Example Title',
            'description' => 'Example Description',
        ],
        fn ($val) => expect($val)->toBe([
            'url' => 'https://jamesdallen.me',
            'image' => '/opengraph/someimage.png',
            'title' => 'Example Title',
            'description' => 'Example Description',
        ]),
    );

    Storage::assertExists('/opengraph/someimage.png');
});

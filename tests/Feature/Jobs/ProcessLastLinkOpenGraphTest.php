<?php

use App\Jobs\ProcessLastLinkOpenGraph;
use App\Models\Feed;
use App\Models\OpenGraph;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

test('it returns an existing open graph if it exists', function () {
    Http::fake();
    Storage::fake();

    $url = 'https://example.com';
    $feed = Feed::factory()
        ->comment()
        ->create();
    OpenGraph::factory()
        ->state([
            'url' => $url,
            'path' => 'opengraph/derp.png',
            'title' => 'Derp title',
        ])
        ->create();

    ProcessLastLinkOpenGraph::dispatch(url: $url, feed: $feed);
    $this->assertTrue(collect($feed->fresh()->meta)->contains('Derp title'));
    $this->assertTrue(collect($feed->fresh()->meta)->contains('/storage/opengraph/derp.png'));
});

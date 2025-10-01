<?php

namespace App\Jobs;

use App\Actions\OpenGraph\GetLinkMetadata;
use App\Actions\OpenGraph\MoveImageToStorage;
use App\Actions\OpenGraph\ProcessImage;
use App\Models\Feed;
use App\Models\OpenGraph;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessLastLinkOpenGraph implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $url,
        public Feed $feed,
    ) {}

    public function handle(): void
    {
        $openGraph = OpenGraph::where('url', $this->url)->first()
            ?? Pipeline::send($this->url)
                ->through([
                    GetLinkMetadata::class,
                    ProcessImage::class,
                    MoveImageToStorage::class,
                ])
                ->then(function ($ogProperties) {
                    if (
                        ! count($ogProperties)
                        || ! data_get($ogProperties, 'title')
                        || ! data_get($ogProperties, 'url')
                        || ! data_get($ogProperties, 'image')
                        || ! data_get($ogProperties, 'description')
                    ) {
                        return;
                    }

                    return OpenGraph::create([
                        'url' => data_get($ogProperties, 'url'),
                        'path' => data_get($ogProperties, 'image'),
                        'disk' => config('filesystems.default', 'public'),
                        'title' => Str::limit(data_get($ogProperties, 'title'), 230),
                        'description' => Str::limit(data_get($ogProperties, 'description'), 230),
                    ]);
                });

        $this->feed->update([
            'meta->ogImageUrl' => Storage::disk($openGraph->disk)->url($openGraph->path),
            'meta->ogDescription' => $openGraph->description,
            'meta->ogTitle' => $openGraph->title,
            'meta->lastLinkUrl' => $openGraph->url,
        ]);
    }
}

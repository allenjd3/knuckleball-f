<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\OpenGraph;
use App\Models\PostalMail;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\Storage;

class CreateFeedItem
{
    public static function execute(PostalMail|Comment $feedItem, ?string $comment)
    {
        $overrides = [];
        if ($ogProperties = CreateFeedItem::processImagesFromComment($feedItem)) {
            $overrides = [
                'ogImageUrl' => Storage::disk($ogProperties->disk)->url($ogProperties->path),
                'ogTitle' => $ogProperties->title,
                'ogDescription' => $ogProperties->description,
                'lastLinkUrl' => $ogProperties->url,
            ];
        }

        $feedItem->feeds()->create([
            'comment' => $comment ?? '',
            'followable_id' => $feedItem->getFollowableId(),
            'meta' => $feedItem->generateMeta($overrides),
        ]);
    }

    private static function processImagesFromComment(PostalMail|Comment $feedItem): ?OpenGraph
    {
        if (! ($feedItem instanceof Comment)) {
            return null;
        }

        $url = GetLastLinkFromBody::handle($feedItem->body);

        if (! $url) {
            return null;
        }

        if ($openGraph = OpenGraph::where('url', $url)->first()) {
            return $openGraph;
        }

        return Pipeline::send($url)
            ->through([
                GetLinkMetadata::class,
                ProcessImage::class,
                MoveImageToStorage::class,
            ])
            ->then(fn ($ogProperties) => count($ogProperties) ? OpenGraph::create([
                'url' => data_get($ogProperties, 'url'),
                'path' => data_get($ogProperties, 'image'),
                'disk' => config('filesystems.default', 'public'),
                'title' => data_get($ogProperties, 'title'),
                'description' => data_get($ogProperties, 'description'),
            ]) : null);
    }
}

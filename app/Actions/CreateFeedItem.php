<?php

namespace App\Actions;

use App\Jobs\ProcessLastLinkOpenGraph;
use App\Models\Comment;
use App\Models\PostalMail;

class CreateFeedItem
{
    public static function execute(PostalMail|Comment $feedItem, ?string $comment)
    {
        $feed = $feedItem->feeds()->create([
            'comment' => $comment ?? '',
            'followable_id' => $feedItem->getFollowableId(),
            'meta' => $feedItem->generateMeta(),
        ]);

        if ($lastLink = CreateFeedItem::getLastLink($feedItem)) {
            ProcessLastLinkOpenGraph::dispatch(url: $lastLink, feed: $feed);
        }
    }

    private static function getLastLink(PostalMail|Comment $feedItem): ?string
    {
        if (! ($feedItem instanceof Comment)) {
            return null;
        }

        return GetLastLinkFromBody::handle($feedItem->body);
    }
}

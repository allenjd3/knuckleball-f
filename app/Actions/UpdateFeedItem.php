<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Feed;
use App\Models\PostalMail;
use App\Traits\ProcessLastLinkable;

class UpdateFeedItem
{
    use ProcessLastLinkable;

    public static function execute(PostalMail|Comment $feedItem, ?string $comment)
    {
        $feed = Feed::firstWhere('feedable_id', $feedItem->id);
        if (! $feed) {
            return;
        }
        $feed->update([
            'comment' => $comment ?? '',
            'followable_id' => $feedItem->getFollowableId(),
            'meta' => $feedItem->generateMeta(),
        ]);

        self::processLastLink($feedItem, $feed);
    }
}

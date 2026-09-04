<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Feed;
use App\Models\InPersonAutograph;
use App\Models\PostalMail;
use App\Traits\ProcessLastLinkable;

class UpdateFeedItem
{
    use ProcessLastLinkable;

    public static function execute(PostalMail|Comment|InPersonAutograph $feedItem, ?string $comment)
    {
        // feedable_id alone isn't unique across feedable types — PostalMail,
        // Comment, and InPersonAutograph all auto-increment independently,
        // so id collisions between them are routine, not edge cases. Without
        // scoping by feedable_type too, updating one record could silently
        // overwrite a completely unrelated feed post's content.
        $feed = Feed::where('feedable_type', $feedItem->getMorphClass())
            ->where('feedable_id', $feedItem->id)
            ->first();

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

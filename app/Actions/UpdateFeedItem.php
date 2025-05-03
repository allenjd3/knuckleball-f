<?php

namespace App\Actions;

use App\Models\Feed;
use App\Models\PostalMail;

class UpdateFeedItem
{
    public static function execute(PostalMail $feedItem, ?string $comment)
    {
        Feed::firstWhere('feedable_id', $feedItem->id)
            ->update([
                'comment' => $comment ?? '',
                'followable_id' => $feedItem->getFollowableId(),
                'meta' => $feedItem->generateMeta(),
            ]);
    }
}

<?php

namespace App\Actions;

use App\Models\PostalMail;

class CreateFeedItem
{
    public static function execute(PostalMail $feedItem, ?string $comment)
    {
        $feedItem->feeds()->create([
            'comment' => $comment ?? "",
            'followable_id' => $feedItem->getFollowableId(),
            'meta' => $feedItem->generateMeta(),
        ]);
    }
}

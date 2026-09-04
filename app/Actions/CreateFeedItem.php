<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\InPersonAutograph;
use App\Models\PostalMail;
use App\Traits\ProcessLastLinkable;

class CreateFeedItem
{
    use ProcessLastLinkable;

    public static function execute(PostalMail|Comment|InPersonAutograph $feedItem, ?string $comment)
    {
        $feed = $feedItem->feeds()->create([
            'comment' => $comment ?? '',
            'followable_id' => $feedItem->getFollowableId(),
            'meta' => $feedItem->generateMeta(),
        ]);

        self::processLastLink($feedItem, $feed);

        return $feed;
    }
}

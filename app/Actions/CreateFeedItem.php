<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Feed;
use App\Models\PostalMail;
use App\Traits\ProcessLastLinkable;

class CreateFeedItem
{
    use ProcessLastLinkable;

    public static function execute(PostalMail|Comment $feedItem, ?string $comment)
    {
        $feed = $feedItem->feeds()->create([
            'comment' => $comment ?? '',
            'followable_id' => $feedItem->getFollowableId(),
            'meta' => $feedItem->generateMeta(),
        ]);

        static::createMentions($comment, $feed);

        self::processLastLink($feedItem, $feed);
    }

    private static function createMentions(string $comment, Feed $feed)
    {
        $mentions = GetMentions::handle($comment);
        $mentions->map(fn ($user) => $feed->mention($user));

        $feed->update([
            'comment' => ReplaceMentions::handle($feed->comment, $mentions)
        ]);
    }
}

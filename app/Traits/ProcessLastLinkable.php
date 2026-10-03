<?php

namespace App\Traits;

use App\Actions\GetLastLinkFromBody;
use App\Jobs\ProcessLastLinkOpenGraph;
use App\Models\AddressRequest;
use App\Models\Comment;
use App\Models\Feed;
use App\Models\InPersonAutograph;
use App\Models\PostalMail;

trait ProcessLastLinkable
{
    public static function processLastLink(PostalMail|Comment|InPersonAutograph|AddressRequest $feedItem, Feed $feed)
    {
        if ($lastLink = self::getLastLink($feedItem)) {
            lockTempDir();
            ProcessLastLinkOpenGraph::dispatch(url: $lastLink, feed: $feed);
        }
    }

    private static function getLastLink(PostalMail|Comment|InPersonAutograph|AddressRequest $feedItem): ?string
    {
        if (! ($feedItem instanceof Comment)) {
            return null;
        }

        return GetLastLinkFromBody::handle($feedItem->body);
    }
}

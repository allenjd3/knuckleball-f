<?php

namespace App\Listeners;

use App\Events\PostalMailDeleted;
use App\Models\Feed;

class DeleteAssociatedFeed
{
    public function handle(PostalMailDeleted $event): void
    {
        Feed::firstWhere('feedable_id', $event->feedableId)->delete();
    }
}

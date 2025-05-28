<?php

namespace App\Listeners;

use App\Events\CommentDeleted;
use App\Events\PostalMailDeleted;
use App\Models\Feed;

class DeleteAssociatedFeed
{
    public function handle(PostalMailDeleted|CommentDeleted $event): void
    {
        Feed::where('feedable_type', $this->type($event))
            ->where('feedable_id', $event->feedableId)
            ->first()
            ?->delete();
    }

    private function type($event)
    {
        return match (true) {
            $event instanceof PostalMailDeleted => 'App\Models\PostalMail',
            $event instanceof CommentDeleted => 'App\Models\Comment',
        };
    }
}

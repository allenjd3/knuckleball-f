<?php

namespace App\Actions;

use App\Jobs\DispatchWatchlistAlerts;
use App\Models\Event;
use App\Models\Feed;
use App\Notifications\EventApprovedNotification;

class CreateEventFeedItem
{
    public static function execute(Event $event): void
    {
        // Digest: if another event feed for this user was created in the last 60 minutes, attach to it
        $recentDigest = Feed::where('feedable_type', Event::class)
            ->where('followable_id', $event->user_id)
            ->where('created_at', '>=', now()->subMinutes(60))
            ->latest()
            ->first();

        if ($recentDigest) {
            $existing = $recentDigest->meta['event_ids'] ?? [];
            if (! in_array($event->id, $existing)) {
                $existing[] = $event->id;
                $recentDigest->update([
                    'meta' => array_merge($recentDigest->meta, ['event_ids' => $existing]),
                ]);
            }
            return;
        }

        Feed::create([
            'followable_id'  => $event->user_id,
            'feedable_type'  => Event::class,
            'feedable_id'    => $event->id,
            'comment'        => '',
            'meta'           => array_merge($event->generateMeta(), ['event_ids' => [$event->id]]),
        ]);

        // Notify submitter
        $event->user->notify(new EventApprovedNotification($event));

        // Watchlist & card show alerts dispatched asynchronously
        DispatchWatchlistAlerts::dispatch($event->id);
    }
}

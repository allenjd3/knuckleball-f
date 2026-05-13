<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\Feed;
use App\Models\User;
use App\Notifications\CardShowAlert;
use App\Notifications\EventApprovedNotification;
use App\Notifications\WatchlistSigningAlert;
use App\Services\GeocodingService;

class CreateEventFeedItem
{
    public static function execute(Event $event): void
    {
        // Digest: if another event feed was created in the last 60 minutes, attach to it
        $recentDigest = Feed::where('feedable_type', Event::class)
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

        // Watchlist & card show alerts
        static::dispatchWatchlistAlerts($event);
    }

    private static function dispatchWatchlistAlerts(Event $event): void
    {
        if ($event->type === 'player_signing' && $event->player_id) {
            // Notify users watching this player
            $watchers = User::whereHas('watchlist', fn ($q) => $q->where('player_id', $event->player_id))->get();

            foreach ($watchers as $user) {
                if ($event->isMailIn()) {
                    // Mail-in: always notify
                    $user->notify(new WatchlistSigningAlert($event));
                } elseif ($event->latitude && $event->longitude && $user->zip_code) {
                    // In-person: only notify if within user's radius
                    $coords = app(GeocodingService::class)->geocodeZip($user->zip_code, $user->country ?? 'US');
                    if ($coords) {
                        $distance = static::haversineDistance(
                            $coords['latitude'], $coords['longitude'],
                            (float) $event->latitude, (float) $event->longitude
                        );
                        if ($distance <= $user->radius) {
                            $user->notify(new WatchlistSigningAlert($event));
                        }
                    }
                }
            }
        }

        if ($event->type === 'card_show' && $event->latitude && $event->longitude) {
            $alertUsers = User::where('card_show_alerts', true)->whereNotNull('zip_code')->get();

            foreach ($alertUsers as $user) {
                $coords = app(GeocodingService::class)->geocodeZip($user->zip_code, $user->country ?? 'US');
                if ($coords) {
                    $distance = static::haversineDistance(
                        $coords['latitude'], $coords['longitude'],
                        (float) $event->latitude, (float) $event->longitude
                    );
                    if ($distance <= $user->radius) {
                        $user->notify(new CardShowAlert($event));
                    }
                }
            }
        }
    }

    private static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 3959; // miles
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * asin(sqrt($a));
    }
}

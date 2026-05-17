<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\User;
use App\Notifications\CardShowAlert;
use App\Notifications\WatchlistSigningAlert;
use App\Services\GeocodingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchWatchlistAlerts implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $eventId) {}

    public function handle(GeocodingService $geocoding): void
    {
        $event = Event::find($this->eventId);

        if (! $event) return;

        if ($event->type === 'player_signing' && $event->player_id) {
            $watchers = User::whereHas('watchlist', fn ($q) => $q->where('player_id', $event->player_id))->get();

            foreach ($watchers as $user) {
                if ($event->isMailIn()) {
                    $user->notify(new WatchlistSigningAlert($event));
                } elseif ($event->latitude && $event->longitude && $user->zip_code) {
                    $coords = $geocoding->geocodeZip($user->zip_code, $user->country ?? 'US');
                    if ($coords) {
                        $distance = $this->haversineDistance(
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
                $coords = $geocoding->geocodeZip($user->zip_code, $user->country ?? 'US');
                if ($coords) {
                    $distance = $this->haversineDistance(
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

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 3959; // miles
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * asin(sqrt($a));
    }
}

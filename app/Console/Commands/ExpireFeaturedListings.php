<?php

namespace App\Console\Commands;

use App\Models\FeaturedListing;
use App\Notifications\FeaturedListingExpired;
use Illuminate\Console\Command;

class ExpireFeaturedListings extends Command
{
    protected $signature = 'featured:expire';
    protected $description = 'Mark expired featured listings and notify owners';

    public function handle(): void
    {
        $expired = FeaturedListing::with(['event.user'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->whereHas('event', fn ($q) => $q->where('is_featured', true))
            ->get();

        $notified = [];

        foreach ($expired as $listing) {
            $event = $listing->event;
            if (! $event || in_array($event->id, $notified)) {
                continue;
            }

            // Only un-feature if there's no other active listing for this event
            $hasActiveOther = FeaturedListing::where('event_id', $event->id)
                ->where('id', '!=', $listing->id)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->exists();

            if (! $hasActiveOther) {
                $event->update(['is_featured' => false]);
                $event->user?->notify(new FeaturedListingExpired($event));
                $notified[] = $event->id;
                $this->line("Expired: {$event->name}");
            }
        }

        $this->info('Done.');
    }
}

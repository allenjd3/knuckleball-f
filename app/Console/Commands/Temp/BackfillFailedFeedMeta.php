<?php

namespace App\Console\Commands\Temp;

use App\Jobs\GenerateReturnCard;
use App\Models\Feed;
use App\Models\PostalMail;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

#[Signature('operation:backfill-failed-feed-meta')]
#[Description('One off command to flag existing failed postal mail feeds and rebuild their share cards')]
class BackfillFailedFeedMeta extends Command
{
    public function handle(): int
    {
        $flagged = 0;
        $regenerated = 0;

        Feed::query()
            ->where('feedable_type', PostalMail::class)
            ->whereIn('feedable_id', PostalMail::query()->where('is_failed', true)->select('id'))
            ->with('feedable')
            ->chunkById(500, function (Collection $feeds) use (&$flagged, &$regenerated): void {
                $feeds
                    ->reject(fn (Feed $feed): bool => data_get($feed->meta, 'is_failed') === true)
                    ->each(function (Feed $feed) use (&$flagged, &$regenerated): void {
                        $feed->meta = [...($feed->meta ?? []), 'is_failed' => true];
                        $feed->saveQuietly();
                        $flagged++;

                        if ($feed->feedable?->returned_date) {
                            GenerateReturnCard::dispatch($feed->feedable_id);
                            $regenerated++;
                        }
                    });
            });

        $this->info("Flagged {$flagged} failed feed " . str('item')->plural($flagged) . " and queued {$regenerated} share " . str('card')->plural($regenerated) . ' to rebuild.');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands\Temp;

use App\Models\Feed;
use App\Models\PostalMail;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

#[Signature('operation:backfill-failed-feed-meta')]
#[Description('One off command to flag existing failed postal mail feeds so they render as failed returns')]
class BackfillFailedFeedMeta extends Command
{
    public function handle(): int
    {
        $updated = 0;

        Feed::query()
            ->where('feedable_type', PostalMail::class)
            ->whereIn('feedable_id', PostalMail::query()->where('is_failed', true)->select('id'))
            ->chunkById(500, function (Collection $feeds) use (&$updated) {
                $feeds
                    ->reject(fn (Feed $feed) => data_get($feed->meta, 'is_failed') === true)
                    ->each(function (Feed $feed) use (&$updated) {
                        $feed->meta = [...$feed->meta, 'is_failed' => true];
                        $feed->saveQuietly();
                        $updated++;
                    });
            });

        $this->info("Flagged {$updated} failed feed " . str('item')->plural($updated) . '.');

        return self::SUCCESS;
    }
}

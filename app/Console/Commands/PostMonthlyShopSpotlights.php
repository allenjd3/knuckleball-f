<?php

namespace App\Console\Commands;

use App\Actions\CreateShopSpotlightFeedItem;
use App\Models\CardShop;
use Illuminate\Console\Command;

class PostMonthlyShopSpotlights extends Command
{
    protected $signature   = 'shops:post-spotlights';
    protected $description = 'Post monthly feed spotlights for featured card shops';

    public function handle(): void
    {
        $shops = CardShop::approved()->where('is_featured', true)->get();

        if ($shops->isEmpty()) {
            $this->info('No featured shops to spotlight.');
            return;
        }

        foreach ($shops as $shop) {
            CreateShopSpotlightFeedItem::execute($shop);
        }

        $this->info("Posted spotlights for {$shops->count()} featured shop(s).");
    }
}

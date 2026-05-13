<?php

namespace App\Actions;

use App\Models\CardShop;
use App\Models\Feed;

class CreateShopSpotlightFeedItem
{
    public static function execute(CardShop $shop): void
    {
        // Digest: collapse multiple shop approvals within 60 minutes into one feed item
        $recentDigest = Feed::where('feedable_type', CardShop::class)
            ->where('created_at', '>=', now()->subMinutes(60))
            ->latest()
            ->first();

        if ($recentDigest) {
            $existing = $recentDigest->meta['shop_ids'] ?? [];
            if (! in_array($shop->id, $existing)) {
                $existing[] = $shop->id;
                $recentDigest->update([
                    'meta' => array_merge($recentDigest->meta, ['shop_ids' => $existing]),
                ]);
            }
            return;
        }

        Feed::create([
            'followable_id' => $shop->user_id,
            'feedable_type' => CardShop::class,
            'feedable_id'   => $shop->id,
            'comment'       => '',
            'meta'          => array_merge($shop->generateMeta(), ['shop_ids' => [$shop->id]]),
        ]);
    }
}

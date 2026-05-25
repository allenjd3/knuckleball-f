<?php

namespace App\View\Components\Feeds;

use App\Models\Feed;
use Illuminate\View\Component;

class ShopSpotlight extends Component
{
    public string $shopName;
    public string $shopPath;
    public string $cityState;
    public ?string $heroPhoto;
    public array $shopIds;

    public function __construct(public Feed $feed)
    {
        $meta = $feed->meta;

        $this->shopName = $meta['shop_name'] ?? '';
        $this->shopPath = $meta['shop_path'] ?? '#';
        $this->cityState = collect([$meta['city'] ?? null, $meta['state'] ?? null])->filter()->implode(', ');
        $this->heroPhoto = $meta['hero_photo'] ?? null;
        $this->shopIds = $meta['shop_ids'] ?? [];
    }

    public function render()
    {
        return view('components.feeds.shop-spotlight');
    }
}

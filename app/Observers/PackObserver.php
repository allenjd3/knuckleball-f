<?php

namespace App\Observers;

use App\Models\Feed;
use App\Models\Pack;

class PackObserver
{
    public function created(Pack $pack): void
    {
        if (! $pack->is_public) {
            return;
        }
        $this->pushToFeed($pack);
    }

    public function updated(Pack $pack): void
    {
        if ($pack->wasChanged('is_public') && $pack->is_public) {
            $this->pushToFeed($pack);
        }
    }

    private function pushToFeed(Pack $pack): void
    {
        $pack->loadMissing('user', 'category');

        $packData = [
            'id' => $pack->id,
            'name' => $pack->name,
            'description' => $pack->description,
            'slug' => $pack->slug,
            'cover_image' => $pack->cover_image,
            'path' => $pack->path(),
            'category' => $pack->category?->name,
            'players_count' => $pack->players()->count(),
        ];

        $existing = Feed::where('feedable_type', Pack::class)
            ->where('followable_id', $pack->user_id)
            ->where('created_at', '>=', now()->subMinutes(60))
            ->latest()
            ->first();

        if ($existing) {
            $packs = data_get($existing->meta, 'packs', []);
            $packs[] = $packData;
            $existing->update(['meta' => array_merge($existing->meta ?? [], ['packs' => $packs])]);
        } else {
            Feed::create([
                'followable_id' => $pack->user_id,
                'feedable_type' => Pack::class,
                'feedable_id' => $pack->id,
                'comment' => '',
                'meta' => [
                    'photo' => $pack->user->profile_photo_url,
                    'user' => $pack->user->name,
                    'user_path' => $pack->user->path(),
                    'packs' => [$packData],
                ],
            ]);
        }
    }
}

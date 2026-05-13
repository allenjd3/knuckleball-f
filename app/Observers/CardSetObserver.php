<?php

namespace App\Observers;

use App\Models\CardSet;
use App\Models\Feed;

class CardSetObserver
{
    public function created(CardSet $set): void
    {
        if (! $set->is_public) {
            return;
        }
        $this->pushToFeed($set);
    }

    public function updated(CardSet $set): void
    {
        if ($set->wasChanged('is_public') && $set->is_public) {
            $this->pushToFeed($set);
        }
    }

    private function pushToFeed(CardSet $set): void
    {
        $set->loadMissing('user');

        $setData = [
            'id'               => $set->id,
            'name'             => $set->name,
            'year'             => $set->year,
            'manufacturer'     => $set->manufacturer,
            'description'      => $set->description,
            'slug'             => $set->slug,
            'cover_image'      => $set->cover_image,
            'path'             => $set->path(),
            'entries_count'    => 0,
        ];

        $existing = Feed::where('feedable_type', CardSet::class)
            ->where('followable_id', $set->user_id)
            ->where('created_at', '>=', now()->subMinutes(60))
            ->latest()
            ->first();

        if ($existing) {
            $sets   = data_get($existing->meta, 'sets', []);
            $sets[] = $setData;
            $existing->update(['meta' => array_merge($existing->meta ?? [], ['sets' => $sets])]);
        } else {
            Feed::create([
                'followable_id' => $set->user_id,
                'feedable_type' => CardSet::class,
                'feedable_id'   => $set->id,
                'meta'          => [
                    'photo'     => $set->user->profile_photo_url,
                    'user'      => $set->user->name,
                    'user_path' => $set->user->path(),
                    'sets'      => [$setData],
                ],
            ]);
        }
    }
}

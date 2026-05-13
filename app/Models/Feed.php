<?php

namespace App\Models;

use Database\Factories\FeedFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Pack;
use App\Models\Reaction;

class Feed extends Model
{
    /** @use HasFactory<FeedFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    protected $attributes = [
        'meta' => '[]',
    ];

    public function feedable(): MorphTo
    {
        return $this->morphTo();
    }

    public function componentName(): string
    {
        return match (true) {
            $this->feedable_type === PostalMail::class => $this->postalMailComponentName(),
            $this->feedable_type === Pack::class       => 'feeds.pack-card',
            $this->feedable_type === Comment::class    => 'feeds.comment',
            $this->feedable_type === Event::class      => 'feeds.event-card',
            $this->feedable_type === CardShop::class   => 'feeds.shop-spotlight',
            default                                    => 'feeds.send-card',
        };
    }

    private function postalMailComponentName(): string
    {
        if (data_get($this->meta, 'date_returned')) {
            return 'feeds.celebration-card';
        }
        if (!empty(data_get($this->meta, 'card_photos', []))) {
            return 'feeds.media-card';
        }
        return 'feeds.send-card';
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function feedComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(Mention::class);
    }

    public function mention(User $user)
    {
        if ($this->mentions()->where('user_id', $user->id)->exists()) {
            return;
        }

        $this->mentions()->create([
            'user_id' => $user->id,
            'mentioned_by_id' => $this->followable_id,
            'feed_type' => $this->feedable_type,
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Feed extends Model
{
    /** @use HasFactory<\Database\Factories\FeedFactory> */
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
            $this->feedable_type === PostalMail::class => 'feeds.postal-mail',
            $this->feedable_type === Comment::class => 'feeds.comment',
        };
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

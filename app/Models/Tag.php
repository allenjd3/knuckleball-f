<?php

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    protected $guarded = [];

    public static function categories()
    {
        return [
            'positive' => 'Positive Tags',
            'neutral' => 'Neutral Tags',
            'unpredictable' => 'Unpredictable Tags',
            'negative' => 'Negative Tags',
        ];
    }

    public static function category(string $key)
    {
        $values = [
            'positive' => 'Positive Tags',
            'neutral' => 'Neutral Tags',
            'unpredictable' => 'Unpredictable Tags',
            'negative' => 'Negative Tags',
        ];

        return data_get($values, $key);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class);
    }

    protected function categoryLabel(): Attribute
    {
        return Attribute::get(
            fn ($value, $attributes) => match ($attributes['category']) {
                'positive' => 'Positive Tags',
                'neutral' => 'Neutral Tags',
                'unpredictable' => 'Unpredictable Tags',
                'negative' => 'Negative Tags',
            },
        );
    }

    protected function casts()
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}

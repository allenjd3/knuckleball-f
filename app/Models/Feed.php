<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Feed extends Model
{
    /** @use HasFactory<\Database\Factories\FeedFactory> */
    use HasFactory;

    protected $fillable = [
        'comment',
        'followable_id',
        'meta',
    ];

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
}

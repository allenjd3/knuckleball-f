<?php

namespace App\Models;

use App\Actions\UpdateFeedItem;
use App\Events\CommentDeleted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'body',
        'comment_id',
    ];

    protected static function booted()
    {
        static::updated(function (Comment $comment) {
            UpdateFeedItem::execute($comment, $comment->body);
        });

        static::deleting(function (Comment $comment) {
            CommentDeleted::dispatch($comment->id);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getFollowableId()
    {
        return $this->user_id;
    }

    public function generateMeta()
    {
        $user = $this->user;

        return [
            'photo' => $user->profile_photo_url,
            'user' => $user->name,
            'user_path' => $user->path(),
            'date_sent' => $this->date_sent,
        ];
    }

    public function feeds(): MorphMany
    {
        return $this->morphMany(Feed::class, 'feedable');
    }
}

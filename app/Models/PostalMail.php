<?php

namespace App\Models;

use App\Actions\UpdateFeedItem;
use App\Events\PostalMailDeleted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PostalMail extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_sent',
        'returned_date',
        'fee_material_id',
        'player_id',
        'comment',
    ];

    protected static function booted()
    {
        static::updated(function (PostalMail $postalMail) {
            UpdateFeedItem::execute($postalMail, $postalMail->comment);
        });

        static::deleting(function (PostalMail $postalMail) {
            PostalMailDeleted::dispatch($postalMail->id);
        });
    }

    public function feeMaterials(): BelongsToMany
    {
        return $this->belongsToMany(FeeMaterial::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function card(): HasOne
    {
        return $this->hasOne(Card::class)->latestOfMany()->withDefault(fn () => new NoCard);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function feeds(): MorphMany
    {
        return $this->morphMany(Feed::class, 'feedable');
    }

    public function getFollowableId()
    {
        return $this->user_id;
    }

    public function generateMeta()
    {
        $user = $this->user;
        $player = $this->player;

        return [
            'photo' => $user->profile_photo_url,
            'user' => $user->name,
            'user_path' => $user->path(),
            'player' => $player->name,
            'player_path' => $player->path(),
            'date_sent' => $this->date_sent,
            'date_returned' => $this->returned_date,
        ];
    }

    protected function casts(): array
    {
        return [
            'date_sent' => 'datetime',
            'returned_date' => 'datetime',
        ];
    }
}

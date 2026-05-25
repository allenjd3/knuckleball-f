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

    protected $guarded = [];

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

    public function signer(): BelongsTo
    {
        return $this->belongsTo(Signer::class);
    }

    public function player(): BelongsTo
    {
        return $this->signer->signable();
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

    public function generateMeta(array $overrides = [])
    {
        $user = $this->user;
        $player = $this->player;
        $cards = $this->cards()->with('media')->get();

        $cardPhotos = $cards->flatMap(fn ($card) => $card->media)->pluck('url')->toArray();

        $playerPhoto = null;
        $category = null;
        if ($player instanceof Player) {
            $player->loadMissing('media', 'team.category');
            $playerPhoto = $player->media?->url;
            $category = $player->team?->category?->name;
        }

        return [
            'photo' => $user->profile_photo_url,
            'user' => $user->name,
            'user_path' => $user->path(),
            'player' => $player->name,
            'player_path' => $player->path(),
            'player_photo' => $playerPhoto,
            'category' => $category,
            'date_sent' => $this->date_sent,
            'date_returned' => $this->returned_date,
            'turnaround_days' => $this->returned_date && $this->date_sent
                ? (int) $this->date_sent->diffInDays($this->returned_date)
                : null,
            'card_photos' => $cardPhotos,
            'cards_count' => $cards->count(),
            ...$overrides,
        ];
    }

    public function fail(): bool
    {
        $this->is_failed = true;

        return $this->save();
    }

    protected function casts(): array
    {
        return [
            'date_sent' => 'datetime',
            'returned_date' => 'datetime',
            'is_failed' => 'boolean',
        ];
    }
}

<?php

namespace App\Models;

use App\Actions\UpdateFeedItem;
use App\Events\InPersonAutographDeleted;
use Database\Factories\InPersonAutographFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InPersonAutograph extends Model
{
    /** @use HasFactory<InPersonAutographFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::updated(function (InPersonAutograph $autograph) {
            UpdateFeedItem::execute($autograph, $autograph->comment);
        });

        static::deleting(function (InPersonAutograph $autograph) {
            InPersonAutographDeleted::dispatch($autograph->id);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(Signer::class);
    }

    public function player(): MorphTo
    {
        return $this->signer->signable();
    }

    public function feeMaterial(): BelongsTo
    {
        return $this->belongsTo(FeeMaterial::class);
    }

    public function feeds(): MorphMany
    {
        return $this->morphMany(Feed::class, 'feedable');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'imageable');
    }

    public function getFollowableId()
    {
        return $this->user_id;
    }

    public function generateMeta(array $overrides = [])
    {
        $user = $this->user;
        $player = $this->player;

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
            'obtained_date' => $this->obtained_date,
            'item' => $this->feeMaterial?->name,
            'location' => $this->location,
            'photos' => $this->media()->pluck('url')->toArray(),
            ...$overrides,
        ];
    }

    protected function casts(): array
    {
        return [
            'obtained_date' => 'date',
            'is_declined' => 'boolean',
        ];
    }
}

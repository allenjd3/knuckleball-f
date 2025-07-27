<?php

namespace App\Models;

use App\Support\Collections\PlayerCollection;
use App\Traits\Signable;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[CollectedBy(PlayerCollection::class)]
class Player extends Model
{
    use HasFactory;
    use Signable;

    protected $guarded = [];

    protected static function booted()
    {
        static::created(function (Player $player) {
            $player->signer()->create();
        });

        static::saving(function (Player $player) {
            $player->slug = str($player->name)->slug()->toString();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function lastTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'last_team_id');
    }

    public function getMedia()
    {
        return '#';
    }

    public function media(): MorphOne
    {
        return $this->morphOne(Media::class, 'imageable')->latestOfMany();
    }

    public function address()
    {
        return $this->addresses()->latest()->published()->notRejected()->first();
    }

    public function path(): string
    {
        return route('players.show', $this->id);
    }

    public function latestMail(): HasOne
    {
        return $this->hasOne(PostalMail::class)->latestOfMany();
    }

    public function publish()
    {
        $this->update(['published_at' => now()]);
    }

    public function addTag(int $tagId)
    {
        if (! auth()->user()?->can('assign', Tag::class)) {
            return abort(403);
        }

        $approvedAt = auth()->user()->isSuperAdmin()
            ? now()
            : null;

        $this->tags()->syncWithoutDetaching([$tagId => ['approved_at' => $approvedAt, 'user_id' => auth()->user()->id]]);
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'retired_at' => 'datetime',
        ];
    }

    protected function responseRate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->signer->response_rate,
        );
    }

    protected function feesRequired(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fees()->exists(),
        );
    }
}

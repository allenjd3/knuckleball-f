<?php

namespace App\Models;

use App\Support\Collections\PlayerCollection;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[CollectedBy(PlayerCollection::class)]
class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'team_id',
        'last_team_id',
        'retired_at',
        'published_at',
    ];

    protected static function booted()
    {
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

    public function address(): HasOne
    {
        return $this->hasOne(Address::class)->latestOfMany();
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function path(): string
    {
        return route('players.show', $this->id);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function postalMails(): HasMany
    {
        return $this->hasMany(PostalMail::class);
    }

    public function latestMail(): HasOne
    {
        return $this->hasOne(PostalMail::class)->latestOfMany();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('approved_at');
    }

    public function publish()
    {
        $this->update(['published_at' => now()]);
    }

    public function addTag(int $tagId)
    {
        if (! $this->can('assign', Tag::class)) {
            abort(403);
        }

        $this->tags()->syncWithoutDetaching([$tagId]);
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
        $total = $this->postalMails()->count();
        $returned = $this->postalMails()->whereNotNull('returned_date')->count();

        return Attribute::make(
            get: fn () => $total > 3 ? round(($returned / $total) * 100) . '%' : null,
        );
    }

    protected function feesRequired(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fees()->exists(),
        );
    }
}

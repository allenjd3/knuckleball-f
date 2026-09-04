<?php

namespace App\Models;

use App\Support\Collections\PlayerCollection;
use App\Traits\Signable;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[CollectedBy(PlayerCollection::class)]
class Player extends Model
{
    use HasFactory;
    use HasSlug;
    use Signable;

    /**
     * Generational suffixes to skip over when deriving last_name, so "Cal
     * Ripken Jr." sorts under "Ripken" rather than "Jr.".
     */
    private const NAME_SUFFIXES = ['jr', 'sr', 'ii', 'iii', 'iv', 'v'];

    protected $guarded = [];

    public static function lastNameFrom(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));

        if (count($parts) > 1 && in_array(strtolower(rtrim(end($parts), '.')), self::NAME_SUFFIXES, true)) {
            array_pop($parts);
        }

        return end($parts) ?: $name;
    }

    protected static function booted()
    {
        static::created(function (Player $player) {
            $player->signer()->create();
        });

        // Keep last_name (used to sort the players table alphabetically by
        // last name) in sync with whatever name is saved, so callers never
        // have to remember to set it themselves.
        static::saving(function (Player $player) {
            $player->last_name = static::lastNameFrom($player->name);
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
        return $this->addresses()->latest()->published()->notRejected()->notExpired()->first();
    }

    public function path(): string
    {
        return route('players.show', $this->slug);
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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'retired_at' => 'datetime',
            'is_retired' => 'boolean',
            'deceased_at' => 'datetime',
        ];
    }

    protected function responseRate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->signer->response_rate,
        );
    }

    protected function inPersonResponseRate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->signer->in_person_response_rate,
        );
    }

    protected function feesRequired(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fees()->exists(),
        );
    }

    protected function isNotDeceased(): Attribute
    {
        return Attribute::get(
            get: fn (mixed $value, array $attributes) => is_null(data_get($attributes, 'deceased_at'))
        );
    }

    protected function isCurrentlyRetired(): Attribute
    {
        return Attribute::get(
            get: fn () => $this->is_retired || (! is_null($this->retired_at) && $this->retired_at->isPast())
        );
    }
}

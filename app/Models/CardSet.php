<?php

namespace App\Models;

use App\Enums\SetEntryStatus;
use App\Observers\CardSetObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[ObservedBy([CardSetObserver::class])]
class CardSet extends Model
{
    use HasFactory;
    use HasSlug;

    protected $table = 'sets';

    protected $guarded = [];

    protected $casts = [
        'is_public' => 'boolean',
        'year'      => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(SetEntry::class, 'set_id');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'set_followers', 'set_id', 'user_id')->withTimestamps();
    }

    public function feeds(): MorphMany
    {
        return $this->morphMany(Feed::class, 'feedable');
    }

    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('user_id', $user->id)->exists();
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function path(): string
    {
        return route('sets.show', $this->slug);
    }

    public function completionPercentage(): int
    {
        $total = $this->entries()->count();
        if ($total === 0) {
            return 0;
        }
        $signed = $this->entries()->where('status', SetEntryStatus::HaveItSigned->value)->count();

        return (int) round(($signed / $total) * 100);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}

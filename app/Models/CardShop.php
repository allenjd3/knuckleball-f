<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CardShop extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $shop) {
            if (empty($shop->slug)) {
                $base = Str::slug($shop->name);
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $shop->slug = $slug;
            }
        });
    }

    // ── Relationships ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'card_shop_category');
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_shops');
    }

    public function featuredShop(): HasOne
    {
        return $this->hasOne(FeaturedShop::class)->where('status', 'active')->latestOfMany();
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ShopClaim::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function feed(): HasOne
    {
        return $this->hasOne(Feed::class, 'feedable_id')->where('feedable_type', self::class)->latest();
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function scopeWithinRadius(Builder $query, float $lat, float $lng, int $miles): void
    {
        $query->whereNotNull('latitude')->whereNotNull('longitude')
            ->selectRaw('*, (3959 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$lat, $lng, $lat])
            ->having('distance', '<=', $miles);
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function heroPhoto(): ?string
    {
        $photos = $this->photos ?? [];
        if (! empty($photos)) {
            return Storage::url($photos[0]);
        }

        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function fullAddress(): string
    {
        return collect([$this->address, $this->city, $this->state, $this->zip_code, $this->country !== 'US' ? $this->country : null])
            ->filter()
            ->implode(', ');
    }

    public function path(): string
    {
        return route('shops.show', $this);
    }

    public function googleMapsUrl(): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($this->fullAddress());
    }

    public function isOpenNow(): bool
    {
        $hours = $this->hours ?? [];
        $dayKey = strtolower(now()->format('l')); // e.g. 'monday'
        $dayHours = $hours[$dayKey] ?? null;

        if (! $dayHours || empty($dayHours['open']) || empty($dayHours['close'])) {
            return false;
        }

        $open = now()->setTimeFromTimeString($dayHours['open']);
        $close = now()->setTimeFromTimeString($dayHours['close']);

        return now()->between($open, $close);
    }

    public function todayHours(): ?string
    {
        $hours = $this->hours ?? [];
        $dayKey = strtolower(now()->format('l'));
        $dayHours = $hours[$dayKey] ?? null;

        if (! $dayHours || empty($dayHours['open'])) {
            return 'Closed';
        }

        return $dayHours['open'] . ' – ' . ($dayHours['close'] ?? '?');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function generateMeta(): array
    {
        return [
            'shop_name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'hero_photo' => $this->heroPhoto(),
            'shop_path' => $this->path(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    protected function casts(): array
    {
        return [
            'hours' => 'array',
            'photos' => 'array',
            'is_featured' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'approved_at' => 'datetime',
        ];
    }
}

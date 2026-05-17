<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date'            => 'date',
            'end_date'              => 'date',
            'submission_deadline'   => 'date',
            'expected_return_by'    => 'date',
            'approved_at'           => 'datetime',
            'is_multi_day'          => 'boolean',
            'personalization_allowed' => 'boolean',
            'photo_op_available'    => 'boolean',
            'vip_available'         => 'boolean',
            'registration_required' => 'boolean',
            'return_envelope_required' => 'boolean',
            'is_featured'           => 'boolean',
            'card_show_alerts'      => 'boolean',
            'photos'                => 'array',
            'payment_methods'       => 'array',
            'pricing_items'         => 'array',
            'fees_per_item'         => 'decimal:2',
            'photo_op_price'        => 'decimal:2',
            'vip_price'             => 'decimal:2',
            'latitude'              => 'decimal:7',
            'longitude'             => 'decimal:7',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expectedSigners(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'event_player');
    }

    public function featuredListing(): HasOne
    {
        return $this->hasOne(FeaturedListing::class)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latestOfMany();
    }

    public function feed(): HasOne
    {
        return $this->hasOne(Feed::class, 'feedable_id')->where('feedable_type', self::class);
    }

    public function cardShop(): BelongsTo
    {
        return $this->belongsTo(CardShop::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('start_date', '>=', now()->toDateString());
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function scopePlayerSignings(Builder $query): void
    {
        $query->where('type', 'player_signing');
    }

    public function scopeCardShows(Builder $query): void
    {
        $query->where('type', 'card_show');
    }

    public function scopeInPerson(Builder $query): void
    {
        $query->where(function ($q) {
            $q->whereIn('type', ['card_show', 'comic_con', 'memorabilia_show'])
              ->orWhere(fn ($q) => $q->where('type', 'player_signing')->where('event_subtype', 'in_person'));
        });
    }

    public function scopeComicCons(Builder $query): void
    {
        $query->where('type', 'comic_con');
    }

    public function scopeMemorabiliaShows(Builder $query): void
    {
        $query->where('type', 'memorabilia_show');
    }

    public function scopeMailIn(Builder $query): void
    {
        $query->where('type', 'player_signing')->where('event_subtype', 'mail_in');
    }

    public function scopeWithinRadius(Builder $query, float $lat, float $lng, int $miles): void
    {
        $query->whereNotNull('latitude')->whereNotNull('longitude')
            ->selectRaw("*, (3959 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<=', $miles);
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'player_signing'   => 'Player Signing',
            'card_show'        => 'Card Show',
            'comic_con'        => 'Comic Con',
            'memorabilia_show' => 'Memorabilia Show',
            default            => ucfirst($this->type),
        };
    }

    public function getSubtypeLabel(): string
    {
        return match ($this->event_subtype) {
            'in_person' => 'In Person',
            'mail_in'   => 'Mail In',
            default     => '',
        };
    }

    public function isMailIn(): bool
    {
        return $this->type === 'player_signing' && $this->event_subtype === 'mail_in';
    }

    public function isInPerson(): bool
    {
        return $this->type === 'card_show' || $this->event_subtype === 'in_person';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isFeaturedActive(): bool
    {
        return $this->is_featured && $this->featuredListing()->exists();
    }

    public function formattedDate(): string
    {
        if ($this->is_multi_day && $this->end_date) {
            if ($this->start_date->month === $this->end_date->month) {
                return $this->start_date->format('M j') . '–' . $this->end_date->format('j, Y');
            }
            return $this->start_date->format('M j') . ' – ' . $this->end_date->format('M j, Y');
        }
        return $this->start_date->format('M j, Y');
    }

    public function heroPhoto(): ?string
    {
        // Player signing: use player photo; card show: use first uploaded photo
        if ($this->type === 'player_signing' && $this->player) {
            return $this->player->media?->url
                ? Storage::url($this->player->media->url)
                : null;
        }
        $photos = $this->photos ?? [];
        return ! empty($photos) ? Storage::url($photos[0]) : null;
    }

    public function fullAddress(): string
    {
        return collect([$this->address, $this->city, $this->state, $this->zip_code, $this->country !== 'US' ? $this->country : null])
            ->filter()
            ->implode(', ');
    }

    public function path(): string
    {
        return route('events.show', $this);
    }

    public function googleMapsUrl(): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($this->fullAddress());
    }

    public function generateMeta(): array
    {
        return [
            'event_type'   => $this->type,
            'event_subtype' => $this->event_subtype,
            'name'         => $this->name,
            'date'         => $this->formattedDate(),
            'venue'        => $this->venue_name,
            'city'         => $this->city,
            'state'        => $this->state,
            'country'      => $this->country,
            'fees'         => $this->fees_per_item,
            'player'       => $this->player?->name,
            'player_path'  => $this->player?->path(),
            'player_photo' => $this->player?->media?->url
                ? Storage::url($this->player->media->url)
                : null,
            'hero_photo'   => $this->heroPhoto(),
            'event_path'   => $this->path(),
            'latitude'     => $this->latitude,
            'longitude'    => $this->longitude,
        ];
    }
}

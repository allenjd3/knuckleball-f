<?php

namespace App\Models;

use App\Actions\FlagStaleAddress;
use App\Enums\AddressType;
use App\Notifications\WatchlistContactAdded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'type' => 'mail',
    ];

    protected static function booted(): void
    {
        static::saved(function (Address $address) {
            if (! $address->isLive()) {
                return;
            }

            if ($address->wasRecentlyCreated || $address->wasChanged(['published_at', 'rejected'])) {
                $requesterIds = AddressRequest::query()
                    ->open()
                    ->where('signer_id', $address->signer_id)
                    ->get()
                    ->each(fn (AddressRequest $addressRequest) => $addressRequest->fulfill())
                    ->pluck('user_id');

                $address->notifyWatchers(except: $requesterIds->all());
            }
        });
    }

    /**
     * Tell users watching this player that new contact info is available. Users who
     * requested the address are skipped since they get their own notification.
     *
     * @param  array<int, int>  $except
     */
    public function notifyWatchers(array $except = []): void
    {
        $notification = WatchlistContactAdded::forAddress($this);

        if (! $notification) {
            return;
        }

        User::query()
            ->whereHas('watchlist', fn (Builder $query) => $query->where('players.id', $notification->player->id))
            ->whereNotIn('id', $except)
            ->when($this->user_id, fn (Builder $query) => $query->where('id', '!=', $this->user_id))
            ->chunkById(200, fn ($watchers) => $watchers->each->notify($notification));
    }

    public function isLive(): bool
    {
        return $this->published_at !== null
            && $this->published_at->lte(now())
            && ! $this->rejected
            && ! $this->isExpired();
    }

    public function scopePublished(Builder $builder)
    {
        $builder->where('published_at', '<', now());
    }

    public function scopeLive(Builder $builder): void
    {
        $builder->published()->notRejected()->notExpired();
    }

    public function scopeMailing(Builder $builder): void
    {
        $builder->where('type', AddressType::Mail);
    }

    public function scopeEmail(Builder $builder): void
    {
        $builder->where('type', AddressType::Email);
    }

    public function isEmail(): bool
    {
        return $this->type === AddressType::Email;
    }

    public function scopeUnpublished(Builder $builder)
    {
        $builder->whereNull('published_at');
    }

    public function scopeNotRejected(Builder $builder)
    {
        $builder->where('rejected', false);
    }

    public function scopeNotExpired(Builder $builder)
    {
        $builder->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function rtsReportCount(): int
    {
        return FlagStaleAddress::reportCount($this);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function player()
    {
        return $this->signer?->signable();
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(Signer::class);
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'reject' => 'boolean',
            'type' => AddressType::class,
            'rts_flagged_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models;

use App\Enums\AddressType;
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
                AddressRequest::query()
                    ->open()
                    ->where('signer_id', $address->signer_id)
                    ->each(fn (AddressRequest $addressRequest) => $addressRequest->fulfill());
            }
        });
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
        ];
    }
}

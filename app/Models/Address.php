<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopePublished(Builder $builder)
    {
        $builder->where('published_at', '<', now());
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
        ];
    }
}

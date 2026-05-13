<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedListing extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_at'  => 'datetime',
            'expires_at' => 'datetime',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public static function prices(): array
    {
        return [
            'one_time_signing'  => 9.99,
            'one_time_cardshow' => 19.99,
            'monthly'           => 29.99,
            'yearly'            => 249.00,
        ];
    }

    public static function expiresAt(string $planType): ?\Carbon\Carbon
    {
        return match ($planType) {
            'one_time' => now()->addDays(30),
            'monthly'  => now()->addMonth(),
            'yearly'   => now()->addYear(),
            default    => null,
        };
    }
}

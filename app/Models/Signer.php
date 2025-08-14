<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Signer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function signable(): MorphTo
    {
        return $this->morphTo();
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function postalMails(): HasMany
    {
        return $this->hasMany(PostalMail::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('approved_at', 'user_id');
    }

    protected function responseRate(): Attribute
    {
        $totalReturned = $this->postalMails()->where(function ($query) {
            $query->whereNotNull('returned_date')
                ->orWhere('is_failed', true);
        })->count();

        $returned = $this->postalMails()->whereNotNull('returned_date')->where('is_failed', false)->count();
        $pending = $this->postalMails()->whereNull('returned_date')->where('is_failed', false)->count();

        $successRate = $totalReturned ? round(($returned / $totalReturned) * 100) . '%' : '';
        $isOrAre = $pending === 1 ? 'is' : 'are';

        return Attribute::make(
            get: fn () => ($totalReturned ? "{$successRate} successful. " : '') . "{$pending} {$isOrAre} pending.",
        );
    }
}

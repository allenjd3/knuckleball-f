<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    protected function responseRate(): Attribute
    {
        $total = $this->postalMails()->count();
        $returned = $this->postalMails()->whereNotNull('returned_date')->count();

        return Attribute::make(
            get: fn () => $total > 3 ? round(($returned / $total) * 100) . '%' : null,
        );
    }
}

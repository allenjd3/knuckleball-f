<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Signer extends Model
{
    public function signable(): MorphTo
    {
        return $this->morphTo();
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}

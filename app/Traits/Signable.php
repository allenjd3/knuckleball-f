<?php

namespace App\Traits;

use App\Models\Signer;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Signable
{
    public function signer(): MorphOne
    {
        return $this->morphOne(Signer::class, 'signable');
    }

    public function addresses(): HasMany
    {
        return $this->signer->addresses();
    }
}

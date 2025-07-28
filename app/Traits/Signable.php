<?php

namespace App\Traits;

use App\Models\Signer;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Signable
{
    public function signer(): MorphOne
    {
        return $this->morphOne(Signer::class, 'signable');
    }

    public function postalMails(): HasMany
    {
        return $this->signer->postalMails();
    }

    public function addresses(): HasMany
    {
        return $this->signer->addresses();
    }

    public function fees(): HasMany
    {
        return $this->signer->fees();
    }

    public function tags(): BelongsToMany
    {
        return $this->signer->tags();
    }
}

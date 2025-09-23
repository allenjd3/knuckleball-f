<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mention extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function feeds(): HasMany
    {
        return $this->hasMany(Feed::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}

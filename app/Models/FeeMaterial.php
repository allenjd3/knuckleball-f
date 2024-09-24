<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'published_at',
    ];

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function postalMails(): BelongsToMany
    {
        return $this->belongsToMany(PostalMail::class);
    }
}

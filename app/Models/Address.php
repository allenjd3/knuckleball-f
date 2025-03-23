<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_1',
        'address_2',
        'city',
        'state',
        'postal_code',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}

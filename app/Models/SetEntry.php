<?php

namespace App\Models;

use App\Enums\SetEntryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status'      => SetEntryStatus::class,
        'date_signed' => 'date',
    ];

    public function cardSet(): BelongsTo
    {
        return $this->belongsTo(CardSet::class, 'set_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}

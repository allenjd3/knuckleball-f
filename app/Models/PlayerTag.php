<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PlayerTag extends Pivot
{
    public $timestamps = false;

    protected $fillable = [
        'player_id',
        'tag_id',
        'approved_at',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'published_at',
        'fee_material_id',
    ];

    protected function casts (): array
    {
        return [
            'published_at' => 'timestamp',
        ];
    }

    public function feeMaterial(): BelongsTo
    {
        return $this->belongsTo(FeeMaterial::class);
    }
}

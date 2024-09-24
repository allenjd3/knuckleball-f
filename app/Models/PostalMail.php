<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PostalMail extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_sent',
        'returned_date',
        'fee_material_id',
        'player_id',
    ];

    protected function casts (): array
    {
        return [
            'date_sent' => 'timestamp',
            'returned_date' => 'timestamp',
        ];
    }

    public function feeMaterials(): BelongsToMany
    {
        return $this->belongsToMany(FeeMaterial::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PostalMail extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_sent',
        'returned_date',
        'fee_material_id',
        'player_id',
        'comment',
    ];

    public function feeMaterials(): BelongsToMany
    {
        return $this->belongsToMany(FeeMaterial::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'date_sent' => 'datetime',
            'returned_date' => 'datetime',
        ];
    }
}

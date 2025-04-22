<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function card(): HasOne
    {
        return $this->hasOne(Card::class)->latestOfMany()->withDefault(fn () => new NoCard);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    protected function casts(): array
    {
        return [
            'date_sent' => 'datetime',
            'returned_date' => 'datetime',
        ];
    }

    public function feeds(): MorphMany
    {
        return $this->morphMany(Feed::class, 'feedable');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_1',
        'address_2',
        'player_id',
        'signer_id',
        'user_id',
        'city',
        'state',
        'rejected',
        'published_at',
        'postal_code',
    ];

    public function scopePublished(Builder $builder)
    {
        $builder->where('published_at', '<', now());
    }

    public function scopeUnpublished(Builder $builder)
    {
        $builder->whereNull('published_at');
    }

    public function scopeNotRejected(Builder $builder)
    {
        $builder->where('rejected', false);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(Signer::class);
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'reject' => 'boolean',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'team_id',
    ];

    protected static function booted()
    {
        static::saving(function (Player $player) {
            $player->slug = str($player->name)->slug()->toString();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getMedia()
    {
        return '#';
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'timestamp',
        ];
    }

    public function media(): MorphOne
    {
        return $this->morphOne(Media::class, 'imageable');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'team_id',
        'published_at',
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

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function path(): string
    {
        return route('players.show', $this->id);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function postalMails(): HasMany
    {
        return $this->hasMany(PostalMail::class);
    }
}

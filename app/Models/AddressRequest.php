<?php

namespace App\Models;

use App\Actions\UpdateFeedItem;
use App\Enums\AddressRequestReason;
use App\Notifications\AddressRequestFulfilled;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AddressRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::updated(function (AddressRequest $addressRequest) {
            UpdateFeedItem::execute($addressRequest, $addressRequest->note);
        });

        static::deleting(function (AddressRequest $addressRequest) {
            $addressRequest->feeds()->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(Signer::class);
    }

    public function postalMail(): BelongsTo
    {
        return $this->belongsTo(PostalMail::class);
    }

    public function feeds(): MorphMany
    {
        return $this->morphMany(Feed::class, 'feedable');
    }

    public function scopeOpen(Builder $builder): void
    {
        $builder->whereNull('fulfilled_at');
    }

    public function isFulfilled(): bool
    {
        return $this->fulfilled_at !== null;
    }

    public function fulfill(): void
    {
        if ($this->isFulfilled()) {
            return;
        }

        $this->update(['fulfilled_at' => now()]);

        $this->user?->notify(new AddressRequestFulfilled($this));
    }

    public function getFollowableId(): int
    {
        return $this->user_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function generateMeta(): array
    {
        $user = $this->user;
        $player = $this->signer?->signable;

        return [
            'photo' => $user->profile_photo_url,
            'user' => $user->name,
            'user_path' => $user->path(),
            'player' => $player?->name,
            'player_path' => $player?->path(),
            'reason' => $this->reason->value,
            'fulfilled_at' => $this->fulfilled_at,
        ];
    }

    protected function casts(): array
    {
        return [
            'reason' => AddressRequestReason::class,
            'fulfilled_at' => 'datetime',
        ];
    }
}

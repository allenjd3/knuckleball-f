<?php

namespace App\Models;

use App\Enums\Role;
use App\Traits\HasProfilePhoto;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use Billable;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    public static function generateSlug(string $name): string
    {
        return str()->slug($name . ' ' . substr(str()->random(), 0, 5), '-');
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->slug = User::generateSlug($user->name);
            $user->handle = str($user->slug)->camel();
        });
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function wantLists(): HasMany
    {
        return $this->hasMany(WantList::class);
    }

    public function packs(): HasMany
    {
        return $this->hasMany(Pack::class);
    }

    public function cardSets(): HasMany
    {
        return $this->hasMany(CardSet::class);
    }

    public function postalMails(): HasMany
    {
        return $this->hasMany(PostalMail::class);
    }

    public function inPersonAutographs(): HasMany
    {
        return $this->hasMany(InPersonAutograph::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->role->isAdmin();
    }

    public function isEditor(): bool
    {
        return (bool) $this->role->isEditor();
    }

    public function isPublished(): bool
    {
        return $this->published_at?->isPast() ?? false;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isEditor() || $this->isSuperAdmin();
    }

    public function follow(User $user): array
    {
        return $this->following()->syncWithoutDetaching($user->id);
    }

    public function unfollow(User $user)
    {
        return $this->following()->detach($user->id);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followables', 'followable_id', 'follower_id');
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followables', 'follower_id', 'followable_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function approveTag(SignerTag $signerTag)
    {
        if ($this->cannot('update', $signerTag)) {
            return;
        }

        $signerTag->signer
            ->signable
            ->tags()
            ->updateExistingPivot($signerTag->tag_id, ['approved_at' => now()]);
    }

    public function rejectTag(SignerTag $signerTag)
    {
        if ($this->cannot('update', $signerTag)) {
            return;
        }

        $signerTag->signer
            ->signable
            ->tags()
            ->detach([$signerTag->tag_id]);
    }

    public function path(): string
    {
        return route('users.profile', ['user' => $this]);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function featuredListings(): HasMany
    {
        return $this->hasMany(FeaturedListing::class);
    }

    public function watchlist(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'watchlist_players')->withTimestamps();
    }

    public function isWatching(Player $player): bool
    {
        return $this->watchlist()->where('player_id', $player->id)->exists();
    }

    public function isPromoter(): bool
    {
        return $this->subscribed('promoter');
    }

    public function cardShops(): HasMany
    {
        return $this->hasMany(CardShop::class);
    }

    public function savedShops(): BelongsToMany
    {
        return $this->belongsToMany(CardShop::class, 'saved_shops')->withTimestamps();
    }

    public function isSavedShop(CardShop $shop): bool
    {
        return $this->savedShops()->where('card_shop_id', $shop->id)->exists();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'published_at' => 'datetime',
            'handle_changed_at' => 'datetime',
            'role' => Role::class,
            'card_show_alerts' => 'boolean',
        ];
    }
}

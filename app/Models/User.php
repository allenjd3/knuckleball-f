<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasProfilePhoto;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'published_at',
    ];

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
        });
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function postalMails(): HasMany
    {
        return $this->hasMany(PostalMail::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->super_admin;
    }

    public function isPublished(): bool
    {
        return $this->published_at?->isPast() ?? false;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isSuperAdmin();
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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'published_at' => 'datetime',
        ];
    }
}

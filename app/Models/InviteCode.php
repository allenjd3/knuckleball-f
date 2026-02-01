<?php

namespace App\Models;

use Database\Factories\InviteCodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model
{
    /** @use HasFactory<InviteCodeFactory> */
    use HasFactory;

    protected $guarded = [];

    public function scopeHasCode(Builder $query, string $code): void
    {
        $query->where(fn ($query) => $query->where('is_unlimited', true)->orWhere('remaining', '>', 0))
            ->where('code', $code);
    }

    public function registerLink(): Attribute
    {
        return Attribute::get(
            fn (mixed $value, array $attributes) => route('register', ['invite_code' => data_get($attributes, 'code')])
        );
    }
}

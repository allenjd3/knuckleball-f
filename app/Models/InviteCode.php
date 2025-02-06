<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model
{
    /** @use HasFactory<\Database\Factories\InviteCodeFactory> */
    use HasFactory;

    public function scopeHasCode(Builder $query, string $code): void
    {
        $query->where(fn ($query) => $query->where('is_unlimited', true)->orWhere('remaining', '>', 0))
            ->where('code', $code);
    }
}

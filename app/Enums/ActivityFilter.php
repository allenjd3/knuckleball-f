<?php

namespace App\Enums;

use App\Models\Feed;
use App\Models\PostalMail;
use Illuminate\Database\Eloquent\Builder;

enum ActivityFilter: string
{
    case All = 'all';
    case Pending = 'pending';
    case Returns = 'returns';
    case Failures = 'failures';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::Pending => 'Pending',
            self::Returns => 'Returns',
            self::Failures => 'Failures',
        };
    }

    public function emptyMessage(): string
    {
        return match ($this) {
            self::All => 'No activity yet.',
            self::Pending => 'No pending sends.',
            self::Returns => 'No returns yet.',
            self::Failures => 'No failures.',
        };
    }

    /**
     * Scope a Feed query down to the postal mail entries matching this filter.
     *
     * @param  Builder<Feed>  $query
     * @return Builder<Feed>
     */
    public function apply(Builder $query): Builder
    {
        return match ($this) {
            self::All => $query,
            self::Pending => $query->whereHasMorph('feedable', [PostalMail::class], fn (Builder $q) => $q
                ->whereNull('returned_date')
                ->where('is_failed', false)),
            self::Returns => $query->whereHasMorph('feedable', [PostalMail::class], fn (Builder $q) => $q
                ->whereNotNull('returned_date')
                ->where('is_failed', false)),
            self::Failures => $query->whereHasMorph('feedable', [PostalMail::class], fn (Builder $q) => $q
                ->where('is_failed', true)),
        };
    }
}

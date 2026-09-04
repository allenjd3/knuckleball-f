<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Card;
use App\Models\CardSet;
use App\Models\Pack;
use App\Models\PostalMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    /**
     * @return array<string, array{label: string, user: User, count: int}|null>
     */
    public function compute(): array
    {
        // Only plain scalars (user_id/count/label) are cached — never Eloquent
        // models. Caching model instances is fragile: depending on the cache
        // store, PHP's unserialize() can fail to reconstruct them ("incomplete
        // object" errors) if the class isn't loaded in exactly the same state.
        $entries = Cache::remember('leaderboard', 900, fn () => $this->run());

        $userIds = collect($entries)->filter()->pluck('user_id');
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        return collect($entries)
            ->map(function (?array $entry) use ($users) {
                if (! $entry || ! $users->has($entry['user_id'])) {
                    return null;
                }

                return [
                    'label' => $entry['label'],
                    'user' => $users->get($entry['user_id']),
                    'count' => $entry['count'],
                ];
            })
            ->all();
    }

    /**
     * @return array<string, array{label: string, user_id: int, count: int}|null>
     */
    private function run(): array
    {
        return [
            'sends' => $this->topUser(PostalMail::query()->whereNotNull('date_sent'), 'Most Sends'),
            'returns' => $this->topUser(PostalMail::query()->whereNotNull('returned_date'), 'Most Returns'),
            'addresses' => $this->topUser(Address::query(), 'Most Addresses Added'),
            'cards' => $this->topUser(Card::query(), 'Most Card Images Added'),
            'packs' => $this->topUser(Pack::query(), 'Most Packs'),
            'sets' => $this->topUser(CardSet::query(), 'Most Sets'),
        ];
    }

    /**
     * @return array{label: string, user_id: int, count: int}|null
     */
    private function topUser(Builder $query, string $label): ?array
    {
        $row = $query
            ->whereNotNull('user_id')
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'label' => $label,
            'user_id' => (int) $row->user_id,
            'count' => (int) $row->total,
        ];
    }
}

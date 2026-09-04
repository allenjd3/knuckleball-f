<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Collection;

/**
 * Finds players that look like accidental duplicates: the same name
 * (trimmed, case-insensitive) on the same team.
 */
class DuplicatePlayerFinder
{
    /**
     * Groups of 2+ players sharing a name and team, oldest first within
     * each group.
     *
     * @return Collection<int, Collection<int, Player>>
     */
    public function find(): Collection
    {
        $duplicateKeys = Player::query()
            ->selectRaw('lower(trim(name)) as normalized_name, team_id')
            ->groupBy('normalized_name', 'team_id')
            ->havingRaw('count(*) > 1')
            ->get();

        return $duplicateKeys
            ->map(fn ($key) => Player::query()
                ->whereRaw('lower(trim(name)) = ?', [$key->normalized_name])
                ->where('team_id', $key->team_id)
                ->with(['team', 'media', 'signer.postalMails', 'signer.addresses', 'signer.fees'])
                ->oldest('created_at')
                ->get())
            ->values();
    }

    /**
     * The other players in $player's duplicate group (same name and team),
     * not including $player itself.
     *
     * @return Collection<int, Player>
     */
    public function siblingsOf(Player $player): Collection
    {
        return Player::query()
            ->whereRaw('lower(trim(name)) = ?', [mb_strtolower(trim($player->name))])
            ->where('team_id', $player->team_id)
            ->whereKeyNot($player->id)
            ->get();
    }
}

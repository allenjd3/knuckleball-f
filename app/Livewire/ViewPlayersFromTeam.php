<?php

namespace App\Livewire;

use App\Models\Player;
use App\Models\Team;

class ViewPlayersFromTeam extends ViewPlayers
{
    public string $teamName;

    protected int $teamId;

    public function mount(
        int $team
    ) {
        $this->teamId = $team;
        $this->teamName = Team::select('name')->firstWhere('id', $this->teamId)->name;
    }

    public function query()
    {
        return Player::query()
            ->where('team_id', $this->teamId)
            ->with(['team', 'lastTeam', 'media'])
            ->where('published_at', '<', now()->endOfDay());
    }
}

<?php

namespace App\Livewire;

use App\Models\Player;
use App\Models\Team;

class ViewPlayersFromTeam extends ViewPlayers
{
    public string $teamName;
    public ?string $mediaUrl;

    protected int $teamId;

    public function mount(
        int $team
    ) {
        $this->teamId = $team;
        $teamModel = Team::firstWhere('id', $this->teamId);
        $this->teamName = $teamModel->name;
        $this->mediaUrl = $teamModel->media?->url;
    }

    public function query()
    {
        return Player::query()
            ->where('team_id', $this->teamId)
            ->with(['team', 'lastTeam', 'media'])
            ->where('published_at', '<', now()->endOfDay());
    }
}

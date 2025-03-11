<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Player;

class ViewPlayersFromCategory extends ViewPlayers
{
    public ?Category $category;

    public function mount(?Category $category)
    {
        $this->category = $category;
    }

    public function query()
    {
        return Player::query()
            ->whereHas('team', fn ($query) => $query->where('category_id', $this->category->id))
            ->with(['team', 'lastTeam', 'media'])
            ->where('published_at', '<', now()->endOfDay());
    }
}

<?php

namespace App\Livewire;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    #[Computed]
    public function players(): Collection
    {
        if (strlen(trim($this->query)) < 2) {
            return collect();
        }

        return Player::query()
            ->where('published_at', '<', now()->endOfDay())
            ->where('rejected', false)
            ->where('name', 'like', '%' . $this->query . '%')
            ->with(['team', 'media'])
            ->orderBy('name')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function teams(): Collection
    {
        if (strlen(trim($this->query)) < 2) {
            return collect();
        }

        return Team::query()
            ->where('rejected', false)
            ->published()
            ->where('name', 'like', '%' . $this->query . '%')
            ->with('media')
            ->orderBy('name')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}

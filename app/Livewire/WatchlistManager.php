<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WatchlistManager extends Component
{
    #[Computed]
    public function watchlist()
    {
        return auth()->user()
            ->watchlist()
            ->with(['media'])
            ->withCount(['signer as signing_count' => fn ($q) => $q->whereHas('postalMails')])
            ->get()
            ->map(function ($player) {
                $latestSigning = Event::approved()
                    ->where('player_id', $player->id)
                    ->upcoming()
                    ->orderBy('start_date')
                    ->first();

                $player->latest_signing = $latestSigning;

                return $player;
            });
    }

    public function remove(int $playerId): void
    {
        auth()->user()->watchlist()->detach($playerId);
        unset($this->watchlist);
    }

    public function render()
    {
        return view('livewire.watchlist-manager')
            ->layout('layouts.app');
    }
}

<?php

namespace App\Livewire;

use App\Services\LeaderboardService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Leaderboard extends Component
{
    public function render()
    {
        return view('livewire.leaderboard')
            ->layout('layouts.app');
    }

    #[Computed]
    public function entries()
    {
        return app(LeaderboardService::class)->compute();
    }

    #[Computed]
    public function authUser()
    {
        return auth()->user()?->loadCount('following', 'followers');
    }
}

<?php

namespace App\Livewire;

use App\Models\Team;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ViewTeams extends Component
{
    public function render()
    {
        return view('livewire.view-teams');
    }

    #[Computed]
    public function teams()
    {
        return Team::all();
    }
}

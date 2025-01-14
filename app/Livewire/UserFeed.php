<?php

namespace App\Livewire;

use App\Models\PostalMail;
use Livewire\Attributes\Computed;
use Livewire\Component;

class UserFeed extends Component
{
    public function render()
    {
        return view('livewire.user-feed');
    }

    #[Computed]
    public function feeds()
    {
        return PostalMail::with(['user', 'feeMaterials'])->get();
    }
}

<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class UserProfile extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function render()
    {
        return view('livewire.user-profile');
    }

    #[Computed]
    public function postalMails()
    {
        return $this->user
            ->postalMails()
            ->orderByDesc('date_sent')
            ->with(['player', 'feeMaterials'])
            ->paginate(10);
    }
}

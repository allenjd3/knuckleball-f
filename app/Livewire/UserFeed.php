<?php

namespace App\Livewire;

use App\Models\Player;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class UserFeed extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.user-feed');
    }

    #[Computed]
    public function players()
    {
        return Player::query()
            ->withWhereHas('latestMail')
            ->withCount(['postalMails' => fn ($query) => $query->whereNotNull('returned_date')])
            ->orderByDesc('postal_mails_count')
            ->orderByDesc('created_at')
            ->paginate(10);
    }
}

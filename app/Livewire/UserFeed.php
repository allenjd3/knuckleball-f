<?php

namespace App\Livewire;

use App\Models\Feed;
use App\Models\Player;
use App\Models\PostalMail;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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
    public function feeds()
    {
        return Feed::query()
            ->paginate(20);
    }

    #[Computed]
    public function players()
    {
        return Player::query()
            ->withWhereHas('latestMail')
            ->withCount(
                [
                    'postalMails' => fn ($query) => $query
                        ->when(
                            auth()->check(),
                            fn ($query) => $query->whereIn(
                                'user_id',
                                fn ($query) => $query->select('follower_id')
                                    ->from('followables')
                                    ->where('follower_id', auth()->id())
                            )
                        )
                        ->whereNotNull('returned_date'),
                ]
            )
            ->orderByDesc('postal_mails_count')
            ->orderByDesc('created_at')
            ->paginate(10);
    }
}

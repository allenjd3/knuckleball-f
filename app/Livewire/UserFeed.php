<?php

namespace App\Livewire;

use App\Models\Feed;
use App\Models\Player;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class UserFeed extends Component
{
    use WithPagination;

    public $hasMore = true;

    public function paginationView()
    {
        return 'vendor.pagination.simple-tailwind';
    }

    public function render()
    {
        return view('livewire.user-feed');
    }

    #[Computed]
    public function feeds()
    {
        return Feed::query()
            ->select('feeds.*')
            ->selectSub(
                fn ($query) => $query->selectRaw('1')->whereIn('users.id', auth()->user()?->following->pluck('id')),
                'is_following'
            )
            ->join('users', 'feeds.followable_id', '=', 'users.id')
            ->orderByDesc('is_following')
            ->orderByDesc('created_at')
            ->simplepaginate();
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

<?php

namespace App\Livewire;

use App\Models\Feed;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
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
                fn ($query) => $query
                    ->when(
                        auth()->check(),
                        fn ($query) => $query->selectRaw('1')
                            ->from('users as u')
                            ->whereColumn('u.id', 'feeds.followable_id')
                            ->where(
                                fn ($query) => $query
                                    ->whereIn('u.id', auth()->user()?->following()->select('users.id'))
                                    ->orWhere('u.id', auth()->user()?->id)
                            ),
                        fn ($query) => $query->selectRaw('0'),
                    ), 'is_following'
            )
            ->orderByDesc('is_following')
            ->orderByDesc('created_at')
            ->simplepaginate();
    }

    #[On('feed-updated')]
    public function updateFeed()
    {
        unset($this->feeds);
    }
}

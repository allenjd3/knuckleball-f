<?php

namespace App\Livewire;

use App\Models\Feed;
use Cache;
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
            ->with('feedable')
            ->select('feeds.*')
            ->orderByRaw(
                "
                    CASE
                        WHEN created_at > ? AND followable_id = ? THEN 1
                        ELSE 0
                    END DESC
                ", [now()->subMinutes(30), auth()->user()->id])
            ->when(count($this->getOrderedIds()), fn ($query) => $query->orderByRaw('FIELD(id, ' . $this->getOrderedIds()->implode(',') . ')'))
            ->orderByDesc('created_at')
            ->simplepaginate();
    }

    #[On('feed-updated')]
    public function updateFeed()
    {
        unset($this->feeds);
    }

    private function getOrderedIds()
    {
        if (! auth()->check()) {
            return collect();
        }

        $followCount = auth()->user()->following()->count();

        return Cache::flexible('user-feed-order-' . auth()->user()->id,
            [900, 3600],
            fn () =>
                Feed::query()
                    ->with('feedable')
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
                    ->orderByRaw('(is_following * 0.95 + RAND() * 0.05) DESC')
                    ->orderByDesc('created_at')
                    ->limit($followCount * 3)
                    ->pluck('id')
        );
    }
}

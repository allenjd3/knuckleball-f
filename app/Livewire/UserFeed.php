<?php

namespace App\Livewire;

use App\Models\Feed;
use App\Models\PostalMail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class UserFeed extends Component
{
    public int $perPage = 15;
    public bool $hasMore = true;
    public string $filter = 'global'; // 'following' | 'global'

    public function render()
    {
        return view('livewire.user-feed')
            ->layout('layouts.app');
    }

    #[Computed]
    public function feeds()
    {
        $query = Feed::query()
            ->when(
                $this->filter === 'following' && auth()->check(),
                fn ($q) => $q->whereIn(
                    'followable_id',
                    auth()->user()->following()->pluck('users.id')->push(auth()->id())
                )
            )
            ->withCount('feedComments')
            ->with(['reactions', 'feedable'])
            ->orderByDesc('created_at')
            ->limit($this->perPage + 1);

        $results = $query->get();
        $this->hasMore = $results->count() > $this->perPage;

        return $results->take($this->perPage);
    }

    #[Computed]
    public function trendingStrip()
    {
        return Feed::where('feedable_type', PostalMail::class)
            ->whereExists(fn ($q) => $q
                ->from('postal_mails')
                ->whereColumn('postal_mails.id', 'feeds.feedable_id')
                ->whereNotNull('postal_mails.returned_date')
            )
            ->withCount('reactions')
            ->where('feeds.created_at', '>=', now()->subDays(7))
            ->orderByDesc('reactions_count')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function authUser()
    {
        return auth()->user()?->loadCount('following', 'followers');
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
        unset($this->feeds);
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->perPage = 15;
        unset($this->feeds);
    }

    #[On('feed-updated')]
    public function updateFeed(): void
    {
        unset($this->feeds, $this->trendingStrip);
    }
}

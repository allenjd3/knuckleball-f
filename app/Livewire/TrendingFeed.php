<?php

namespace App\Livewire;

use App\Models\Feed;
use App\Models\Player;
use App\Models\PostalMail;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TrendingFeed extends Component
{
    public int $perPage = 20;
    public int $offset = 0;
    public bool $hasMore = true;
    public bool $hasPrevious = false;
    public int $maxWindow = 60;
    public string $sort = 'reactions'; // 'reactions' | 'fastest' | 'comments'

    public function render()
    {
        return view('livewire.trending-feed')
            ->layout('layouts.app');
    }

    #[Computed]
    public function trendingReturns()
    {
        $query = Feed::where('feedable_type', PostalMail::class)
            ->whereExists(fn ($q) => $q
                ->from('postal_mails')
                ->whereColumn('postal_mails.id', 'feeds.feedable_id')
                ->whereNotNull('postal_mails.returned_date')
            )
            ->where('feeds.created_at', '>=', now()->subDays(30))
            ->withCount('reactions', 'feedComments');

        if ($this->sort === 'reactions') {
            $query->orderByDesc('reactions_count');
        } elseif ($this->sort === 'comments') {
            $query->orderByDesc('feed_comments_count');
        }

        // For 'fastest' we sort in PHP to avoid cross-DB JSON extraction differences; cap at 500 for safety
        if ($this->sort !== 'fastest') {
            $query->skip($this->offset)->limit($this->perPage + 1);
        } else {
            $query->limit(500);
        }

        $results = $query->get();

        if ($this->sort === 'fastest') {
            $results = $results
                ->filter(fn ($f) => ! is_null(data_get($f->meta, 'turnaround_days')))
                ->sortBy(fn ($f) => (int) data_get($f->meta, 'turnaround_days'))
                ->values();

            $this->hasMore = false;
            $this->hasPrevious = false;

            return $results->take($this->perPage);
        }

        $this->hasMore = $results->count() > $this->perPage;
        $this->hasPrevious = $this->offset > 0;

        return $results->take($this->perPage);
    }

    #[Computed]
    public function trendingPlayers()
    {
        $subquery = Player::query()
            ->selectRaw('players.id, COUNT(postal_mails.id) as send_count')
            ->join('signers', fn ($j) => $j
                ->on('signers.signable_id', '=', 'players.id')
                ->where('signers.signable_type', Player::class)
            )
            ->join('postal_mails', fn ($j) => $j
                ->on('postal_mails.signer_id', '=', 'signers.id')
                ->whereNotNull('postal_mails.date_sent')
                ->where('postal_mails.created_at', '>=', now()->subDays(30))
            )
            ->groupBy('players.id')
            ->orderByDesc('send_count')
            ->limit(10);

        return Player::query()
            ->joinSub($subquery, 'counts', fn ($j) => $j->on('players.id', '=', 'counts.id'))
            ->select('players.*', 'counts.send_count')
            ->with('media', 'team.category')
            ->orderByDesc('counts.send_count')
            ->get();
    }

    #[Computed]
    public function authUser()
    {
        return auth()->user()?->loadCount('following', 'followers');
    }

    public function loadMore(): void
    {
        if ($this->perPage < $this->maxWindow) {
            $this->perPage += 20;
        } else {
            $this->offset += 20;
        }
        unset($this->trendingReturns);
    }

    public function loadPrevious(): void
    {
        $this->offset = max(0, $this->offset - 20);
        unset($this->trendingReturns);
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
        $this->perPage = 20;
        $this->offset = 0;
        unset($this->trendingReturns);
    }
}

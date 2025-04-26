<?php

namespace App\Livewire;

use App\Models\Feed;
use App\Models\Player;
use DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserFeed extends Component
{
    use WithPagination;
    public $feeds = [];
    public $cursor = null;
    public $hasMore = true;

    public function mount()
    {
        $this->loadMore(); // Load first set
    }

    public function loadMore()
    {
        $query = Feed::orderByDesc('id');

        $results = $this->cursor
            ? $query->cursorPaginate(10, ['*'], 'cursor', $this->cursor)
            : $query->cursorPaginate(10);

        $this->feeds = [...$this->feeds, ...$results->items()];
        if (count($this->feeds) > 50) {
            array_slice($this->feeds, -50);
        }

        $this->cursor = $results->nextCursor()?->encode();
        $this->hasMore = $results->hasMorePages();
    }

    public function render()
    {
        return view('livewire.user-feed');
    }

    #[Computed]
    public function feeds()
    {
        return Feed::query()
            ->addSelect('1 as poo')
            ->get();

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

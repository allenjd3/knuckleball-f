<?php

namespace App\Livewire;

use App\Models\CardSet;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BrowseSets extends Component
{
    use WithPagination;

    #[Url]
    public string $sort = 'recent';

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function sets()
    {
        return CardSet::public()
            ->withCount('entries', 'followers')
            ->with('user')
            ->when($this->sort === 'complete', function ($q) {
                $q->withCount(['entries as signed_count' => fn ($q) => $q->where('status', 'have_it_signed')])
                    ->orderByRaw('CASE WHEN entries_count = 0 THEN 0 ELSE signed_count / entries_count END DESC');
            })
            ->when($this->sort === 'followed', fn ($q) => $q->orderByDesc('followers_count'))
            ->when($this->sort === 'recent', fn ($q) => $q->latest())
            ->paginate(24);
    }

    public function render()
    {
        return view('livewire.browse-sets')
            ->layout('layouts.app');
    }
}

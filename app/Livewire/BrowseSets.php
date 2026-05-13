<?php

namespace App\Livewire;

use App\Models\CardSet;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class BrowseSets extends Component
{
    #[Url]
    public string $sort = 'recent';

    #[Computed]
    public function sets()
    {
        return CardSet::public()
            ->withCount('entries', 'followers')
            ->with('user')
            ->when($this->sort === 'complete', function ($q) {
                // Sort by completion % (signed / total entries) desc
                $q->withCount(['entries as signed_count' => fn ($q) => $q->where('status', 'have_it_signed')])
                    ->orderByRaw('CASE WHEN entries_count = 0 THEN 0 ELSE signed_count / entries_count END DESC');
            })
            ->when($this->sort === 'followed', fn ($q) => $q->orderByDesc('followers_count'))
            ->when($this->sort === 'recent', fn ($q) => $q->latest())
            ->get();
    }

    public function render()
    {
        return view('livewire.browse-sets')
            ->layout('layouts.app');
    }
}

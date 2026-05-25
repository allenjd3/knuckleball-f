<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Pack;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BrowsePacks extends Component
{
    use WithPagination;

    #[Url]
    public ?int $category = null;

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function packs()
    {
        return Pack::public()
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->withCount('players', 'followers')
            ->with('user', 'category')
            ->latest()
            ->paginate(24);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.browse-packs')
            ->layout('layouts.app');
    }
}

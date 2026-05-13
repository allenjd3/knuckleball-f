<?php

namespace App\Livewire;

use App\Models\CardShop;
use App\Models\Category;
use App\Services\GeocodingService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CardShopsLanding extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'all';

    #[Url]
    public string $search = '';

    #[Url]
    public int $categoryId = 0;

    public bool $showMap = false;

    #[Computed]
    public function shops()
    {
        $query = CardShop::approved()
            ->with(['categories', 'featuredShop'])
            ->orderByDesc('is_featured')
            ->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%')
                  ->orWhere('state', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoryId) {
            $query->whereHas('categories', fn ($q) => $q->where('category_id', $this->categoryId));
        }

        if ($this->filter === 'near_me') {
            $this->applyNearMe($query);
        }

        return $query->paginate(20);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function mapLocations(): array
    {
        return CardShop::approved()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(fn (CardShop $s) => [
                'name'      => $s->name,
                'latitude'  => (float) $s->latitude,
                'longitude' => (float) $s->longitude,
                'popup'     => "<strong>{$s->name}</strong><br>{$s->city}, {$s->state}<br><a href='{$s->path()}'>View →</a>",
            ])
            ->toArray();
    }

    private function applyNearMe($query): void
    {
        $user = auth()->user();
        $zip  = $user?->zip_code;

        if (! $zip) return;

        $coords = app(GeocodingService::class)->geocodeZip($zip, $user->country ?? 'US');
        if (! $coords) return;

        $radius = $user->radius ?? 50;
        $query->withinRadius($coords['latitude'], $coords['longitude'], $radius);
    }

    public function updatedFilter(): void { $this->resetPage(); unset($this->shops); }
    public function updatedSearch(): void { $this->resetPage(); unset($this->shops); }
    public function updatedCategoryId(): void { $this->resetPage(); unset($this->shops); }

    public function render()
    {
        return view('livewire.card-shops-landing')
            ->layout('layouts.app');
    }
}

<?php

namespace App\Livewire;

use App\Models\Event;
use App\Services\GeocodingService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EventsLanding extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'all';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public bool $showMap = false;

    public function mount(): void
    {
        $this->dateFrom = now()->toDateString();
        $this->dateTo = now()->addDays(90)->toDateString();
    }

    #[Computed]
    public function events()
    {
        $query = Event::approved()
            ->with(['player.media', 'featuredListing'])
            ->where('start_date', '>=', $this->dateFrom ?: now()->toDateString())
            ->where('start_date', '<=', $this->dateTo ?: now()->addDays(90)->toDateString())
            ->orderByDesc('is_featured')
            ->orderBy('start_date');

        match ($this->filter) {
            'near_me' => $this->applyNearMe($query),
            'player_signing' => $query->playerSignings(),
            'card_show' => $query->cardShows(),
            'comic_con' => $query->comicCons(),
            'memorabilia_show' => $query->memorabiliaShows(),
            'in_person' => $query->inPerson(),
            'mail_in' => $query->mailIn(),
            default => null,
        };

        return $query->paginate(20);
    }

    #[Computed]
    public function mapLocations(): array
    {
        return Event::approved()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('start_date', '>=', $this->dateFrom ?: now()->toDateString())
            ->where('start_date', '<=', $this->dateTo ?: now()->addDays(90)->toDateString())
            ->limit(500)
            ->get()
            ->map(fn (Event $e) => [
                'name' => $e->name,
                'latitude' => (float) $e->latitude,
                'longitude' => (float) $e->longitude,
                'popup' => '<strong>' . e($e->name) . '</strong><br>' . e($e->formattedDate()) . '<br><a href=\'' . e($e->path()) . '\'>View →</a>',
            ])
            ->toArray();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
        unset($this->events);
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
        unset($this->events);
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
        unset($this->events);
    }

    public function render()
    {
        return view('livewire.events-landing')
            ->layout('layouts.app');
    }

    private function applyNearMe($query): void
    {
        $user = auth()->user();
        $zip = $user?->zip_code;

        if (! $zip) {
            return;
        }

        $coords = app(GeocodingService::class)->geocodeZip($zip, $user->country ?? 'US');
        if (! $coords) {
            return;
        }

        $radius = $user->radius ?? 50;
        $query->withinRadius($coords['latitude'], $coords['longitude'], $radius);
    }
}

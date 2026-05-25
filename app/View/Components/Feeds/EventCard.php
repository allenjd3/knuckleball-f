<?php

namespace App\View\Components\Feeds;

use App\Models\Feed;
use Illuminate\View\Component;

class EventCard extends Component
{
    public string $eventType;
    public string $eventName;
    public string $eventPath;
    public string $date;
    public string $venue;
    public string $cityState;
    public ?string $fees;
    public ?string $heroPhoto;
    public bool $isFeatured;
    public array $eventIds;

    public function __construct(public Feed $feed)
    {
        $meta = $feed->meta;

        $this->eventType = $meta['event_type'] ?? 'player_signing';
        $this->eventName = $meta['name'] ?? '';
        $this->eventPath = $meta['event_path'] ?? '#';
        $this->date = $meta['date'] ?? '';
        $this->venue = $meta['venue'] ?? '';
        $this->cityState = collect([$meta['city'] ?? null, $meta['state'] ?? null])->filter()->implode(', ');
        $this->fees = isset($meta['fees']) ? '$' . number_format((float) $meta['fees'], 2) : null;
        $this->heroPhoto = $meta['hero_photo'] ?? $meta['player_photo'] ?? null;
        $this->isFeatured = $meta['is_featured'] ?? false;
        $this->eventIds = $meta['event_ids'] ?? [];
    }

    public function render()
    {
        return view('components.feeds.event-card');
    }
}

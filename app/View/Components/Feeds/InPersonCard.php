<?php

namespace App\View\Components\Feeds;

use App\Models\Feed;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InPersonCard extends Component
{
    public string $photo;
    public string $user;
    public string $userPath;
    public string $player;
    public string $playerPath;
    public ?string $obtainedDate;
    public ?string $item;
    public ?string $location;
    public ?string $category;
    public array $photos;
    public ?string $heroPhoto;

    public function __construct(public Feed $feed)
    {
        $this->photo = data_get($feed->meta, 'photo', '');
        $this->user = data_get($feed->meta, 'user', '');
        $this->userPath = data_get($feed->meta, 'user_path', '');
        $this->player = data_get($feed->meta, 'player', '');
        $this->playerPath = data_get($feed->meta, 'player_path', '');
        $obtainedDate = data_get($feed->meta, 'obtained_date');
        $this->obtainedDate = $obtainedDate ? Carbon::parse($obtainedDate)->format('M j, Y') : null;
        $this->item = data_get($feed->meta, 'item');
        $this->location = data_get($feed->meta, 'location');
        $this->category = data_get($feed->meta, 'category');
        $this->photos = data_get($feed->meta, 'photos', []);
        $this->heroPhoto = $this->photos[0] ?? null;
    }

    public function render(): View|Closure|string
    {
        return view('components.feeds.in-person-card');
    }
}

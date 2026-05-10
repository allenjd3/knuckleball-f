<?php

namespace App\View\Components\Feeds;

use App\Models\Feed;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CelebrationCard extends Component
{
    public string $photo;
    public string $user;
    public string $userPath;
    public string $player;
    public string $playerPath;
    public string $dateSent;
    public string $dateReturned;
    public ?int $turnaroundDays;
    public array $cardPhotos;
    public ?string $heroPhoto;

    public function __construct(public Feed $feed)
    {
        $this->photo = data_get($feed->meta, 'photo', '');
        $this->user = data_get($feed->meta, 'user', '');
        $this->userPath = data_get($feed->meta, 'user_path', '');
        $this->player = data_get($feed->meta, 'player', '');
        $this->playerPath = data_get($feed->meta, 'player_path', '');
        $this->dateSent = Carbon::parse(data_get($feed->meta, 'date_sent'))->format('M j, Y');
        $this->dateReturned = Carbon::parse(data_get($feed->meta, 'date_returned'))->format('M j, Y');
        $this->turnaroundDays = data_get($feed->meta, 'turnaround_days');
        $this->cardPhotos = data_get($feed->meta, 'card_photos', []);
        $this->heroPhoto = $this->cardPhotos[0] ?? null;
    }

    public function render(): View|Closure|string
    {
        return view('components.feeds.celebration-card');
    }
}

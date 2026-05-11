<?php

namespace App\View\Components\Feeds;

use App\Models\Feed;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SendCard extends Component
{
    public string  $photo;
    public string  $user;
    public string  $userPath;
    public string  $player;
    public string  $playerPath;
    public string  $dateSent;
    public ?string $playerPhoto;
    public ?string $category;
    public int     $cardsCount;

    public function __construct(public Feed $feed)
    {
        $this->photo       = data_get($feed->meta, 'photo', '');
        $this->user        = data_get($feed->meta, 'user', '');
        $this->userPath    = data_get($feed->meta, 'user_path', '');
        $this->player      = data_get($feed->meta, 'player', '');
        $this->playerPath  = data_get($feed->meta, 'player_path', '');
        $this->dateSent    = Carbon::parse(data_get($feed->meta, 'date_sent'))->format('M j, Y');
        $this->playerPhoto = data_get($feed->meta, 'player_photo');
        $this->category    = data_get($feed->meta, 'category');
        $this->cardsCount  = (int) data_get($feed->meta, 'cards_count', 0);
    }

    public function render(): View|Closure|string
    {
        return view('components.feeds.send-card');
    }
}

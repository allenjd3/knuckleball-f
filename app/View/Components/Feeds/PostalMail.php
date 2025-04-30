<?php

namespace App\View\Components\Feeds;

use App\Models\Feed;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PostalMail extends Component
{
    public string $photo;
    public string $user;
    public string $user_path;
    public string $player;
    public string $player_path;
    public string $dateSent;
    public string $dateReturned;
    public string $type;

    public function __construct(
        public Feed $feed,
    ) {
        $this->photo = data_get($feed->meta, 'photo');
        $this->user = data_get($feed->meta, 'user');
        $this->player = data_get($feed->meta, 'player');
        $this->player_path = data_get($feed->meta, 'player_path');
        $this->user_path = data_get($feed->meta, 'user_path');
        $this->dateSent = Carbon::parse(data_get($feed->meta, 'date_sent'))->format('M d, Y');
        $this->dateReturned = Carbon::parse(data_get($feed->meta, 'date_returned'))->format('M d, Y');
        $this->type = data_get($feed->meta, 'type', '');
    }

    public function render(): View|Closure|string
    {
        return view('components.feeds.postal-mail');
    }
}

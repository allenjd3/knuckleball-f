<?php

namespace App\View\Components\Feeds;

use App\Models\Feed;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SetCard extends Component
{
    public string $photo;
    public string $user;
    public string $userPath;
    public array  $sets;
    public string $createdAt;
    public string $cardType;

    public function __construct(public Feed $feed)
    {
        $this->photo     = data_get($feed->meta, 'photo', '');
        $this->user      = data_get($feed->meta, 'user', '');
        $this->userPath  = data_get($feed->meta, 'user_path', '');
        $this->sets      = data_get($feed->meta, 'sets', []);
        $this->createdAt = $feed->created_at->diffForHumans();
        $this->cardType  = data_get($feed->meta, 'card_type', 'created');
    }

    public function render(): View|Closure|string
    {
        return view('components.feeds.set-card');
    }
}

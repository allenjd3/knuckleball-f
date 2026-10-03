<?php

namespace App\View\Components\Feeds;

use App\Enums\AddressRequestReason;
use App\Models\Feed;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddressRequestCard extends Component
{
    public string $photo;
    public string $user;
    public string $userPath;
    public string $player;
    public string $playerPath;
    public AddressRequestReason $reason;
    public bool $isFulfilled;

    public function __construct(public Feed $feed)
    {
        $this->photo = data_get($feed->meta, 'photo', '');
        $this->user = data_get($feed->meta, 'user', '');
        $this->userPath = data_get($feed->meta, 'user_path', '');
        $this->player = data_get($feed->meta, 'player') ?? '';
        $this->playerPath = data_get($feed->meta, 'player_path') ?? '';
        $this->reason = AddressRequestReason::tryFrom((string) data_get($feed->meta, 'reason'))
            ?? AddressRequestReason::MissingAddress;
        $this->isFulfilled = (bool) data_get($feed->meta, 'fulfilled_at');
    }

    public function render(): View|Closure|string
    {
        return view('components.feeds.address-request-card');
    }
}

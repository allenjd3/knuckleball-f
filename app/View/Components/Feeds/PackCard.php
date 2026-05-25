<?php

namespace App\View\Components\Feeds;

use App\Models\Feed;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PackCard extends Component
{
    public string $photo;
    public string $user;
    public string $userPath;
    public array $packs;
    public string $createdAt;

    public function __construct(public Feed $feed)
    {
        $this->photo = data_get($feed->meta, 'photo', '');
        $this->user = data_get($feed->meta, 'user', '');
        $this->userPath = data_get($feed->meta, 'user_path', '');
        $this->packs = data_get($feed->meta, 'packs', []);
        $this->createdAt = $feed->created_at->diffForHumans();
    }

    public function render(): View|Closure|string
    {
        return view('components.feeds.pack-card');
    }
}

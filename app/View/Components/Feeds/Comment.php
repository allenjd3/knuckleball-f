<?php

namespace App\View\Components\Feeds;

use App\Models\Feed;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Comment extends Component
{
    public string $user;
    public string $photo;
    public string $date_sent;
    public string $user_path;

    public function __construct(
        public Feed $feed,
    ) {
        $this->user = data_get($feed->meta, 'user');
        $this->photo = data_get($feed->meta, 'photo');
        $this->date_sent = Carbon::parse(data_get($feed->meta, 'date_sent'))->format('M d, Y');
        $this->user_path = data_get($feed->meta, 'user_path');
    }

    public function render(): View|Closure|string
    {
        return view('components.feeds.comment');
    }
}

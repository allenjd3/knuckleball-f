<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\PostalMail;

class HomeController extends Controller
{
    public function __invoke()
    {
        $tickerItems = Feed::where('feedable_type', PostalMail::class)
            ->where('created_at', '>=', now()->subDays(60))
            ->latest()
            ->limit(20)
            ->get()
            ->map(function (Feed $feed) {
                $meta    = $feed->meta;
                $slug    = basename(data_get($meta, 'user_path', ''));
                $player  = data_get($meta, 'player', '');
                $days    = data_get($meta, 'turnaround_days');
                $isReturn = ! empty(data_get($meta, 'date_returned'));

                return compact('slug', 'player', 'days', 'isReturn');
            })
            ->filter(fn ($item) => $item['slug'] && $item['player'])
            ->values();

        return view('landing', compact('tickerItems'));
    }
}

<?php

namespace App\View\Components\Feeds;

use App\Models\Feed;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PostalMail extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public Feed $feed,
    ){}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.feeds.postal-mail');
    }
}

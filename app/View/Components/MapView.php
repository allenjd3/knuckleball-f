<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MapView extends Component
{
    /**
     * @param array $locations  Each: ['name' => '', 'latitude' => 0.0, 'longitude' => 0.0, 'popup' => '']
     */
    public function __construct(
        public array $locations,
        public int $zoom = 6,
        public string $height = '400px',
    ) {}

    public function render()
    {
        return view('components.map-view');
    }
}

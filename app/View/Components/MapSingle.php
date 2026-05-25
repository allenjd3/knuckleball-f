<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MapSingle extends Component
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public string $name,
        public string $directionsUrl,
        public string $height = '300px',
    ) {}

    public function render()
    {
        return view('components.map-single');
    }
}

<?php

namespace App\Traits;

use Filament\Support\Colors\Color;
use Laravel\Jetstream\HasProfilePhoto as JetstreamHasProfilePhoto;
use Spatie\Color\Rgb;

trait HasProfilePhoto
{
    use JetstreamHasProfilePhoto;

    protected function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=822326&background=F5CECF';
    }
}

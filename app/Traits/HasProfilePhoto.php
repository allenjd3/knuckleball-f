<?php

namespace App\Traits;

use Laravel\Jetstream\HasProfilePhoto as JetstreamHasProfilePhoto;
use Filament\Support\Colors\Color;
use Spatie\Color\Rgb;

trait HasProfilePhoto {
    use JetstreamHasProfilePhoto;

    const BASE_HEX = '#d83c40';

    protected function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color='. $this->createColorValue(shade: 800) .'&background=' . $this->createColorValue(shade: 200);
    }

    private function createColorValue(int $shade): string
    {
        return $this->generateHexValue($this->generateRgbString($shade));
    }

    private function generateRgbString(int $shade): string
    {
        return 'rgb(' . data_get(Color::hex(self::BASE_HEX), $shade) . ')';
    }

    private function generateHexValue(string $rgbString): string
    {
        return substr(Rgb::fromString($rgbString)->toHex(), 1);
    }
}

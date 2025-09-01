<?php

namespace App\Actions\OpenGraph;

use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

class ProcessImage
{
    public function __invoke(array $ogProperties, Closure $next)
    {
        $path = data_get($ogProperties, 'image');

        $absolutePath = Storage::disk('temp')->path($path);
        try {
            Image::load($absolutePath)
                ->fit(Fit::Crop, 1200, 630) // Use fit instead of separate width/height
                ->save($absolutePath);
        } catch (Exception $e) {
            Log::critical('problem with image: ' . $path);
            Log::critical($e->getMessage());
        }

        return $next($ogProperties);
    }
}
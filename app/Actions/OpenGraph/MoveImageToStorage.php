<?php

namespace App\Actions\OpenGraph;

use Closure;
use Illuminate\Support\Facades\Storage;

class MoveImageToStorage
{
    public function __invoke(array $ogProperties, Closure $next)
    {
        $fileName = data_get($ogProperties, 'image');
        Storage::put('opengraph/' . $fileName, Storage::disk('temp')->get($fileName));

        return $next([
            ...$ogProperties,
            'image' => 'opengraph/' . $fileName,
        ]);
    }
}

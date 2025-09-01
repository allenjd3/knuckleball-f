<?php

use Illuminate\Support\Facades\Cache;

if (! function_exists('lockTempDir')) {
    function lockTempDir(int $duration = 60)
    {
        Cache::remember('temp_files_in_use', $duration, fn () => true);
    }
}

if (! function_exists('isTempDirLocked')) {
    function isTempDirLocked(): bool
    {
        return Cache::has('temp_files_in_use');
    }
}

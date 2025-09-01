<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanTempDirectory extends Command
{
    protected $signature = 'temp-directory:clean';
    protected $description = 'Cleans up the temp directory';

    private $tempDisk;

    public function handle()
    {
        $this->tempDisk = Storage::drive('local');
        $allFiles = collect($this->tempDisk->allFiles());

        if ($allFiles->isEmpty()) {
            $this->warn('there are no files to process at this time');

            return;
        }

        if (isTempDirLocked()) {
            $this->warn('currently processing files');

            return;
        }

        $allFiles->each(function ($file) {
            $this->tempDisk->delete($file);
            $this->info('all files deleted successfully');
        });
    }
}

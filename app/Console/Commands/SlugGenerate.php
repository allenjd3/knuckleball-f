<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SlugGenerate extends Command
{
    protected $signature = 'users:slug-generate';

    protected $description = 'This command adds a slug to users that already exist';

    public function handle()
    {
        if ($this->confirm('This will reset all User Slugs. Only run this once! Do you wish to continue?')) {
            User::get()
                ->each(
                    function (User $user) {
                        $user->slug = User::generateSlug($user->name);
                        $user->save();
                    }
                );
        }
    }
}

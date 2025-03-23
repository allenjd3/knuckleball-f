<?php

namespace App\Console\Commands;

use App\Models\Player;
use Illuminate\Console\Command;

class CleanDuplicateAddresses extends Command
{
    protected $signature = 'address:clean-duplicates';

    protected $description = 'Removes duplicate addresses';

    public function handle()
    {
        $this->withProgressBar(
            Player::lazyById(),
            fn (Player $player) => $player->addresses()
                    ->where('addresses.id', '!=', $player->address?->id)
                    ->delete(),
        );
    }
}

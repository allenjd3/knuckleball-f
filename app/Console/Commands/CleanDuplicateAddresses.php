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
            function (Player $player) {
                $player->addresses()
                    ->mailing()
                    ->where('addresses.id', '!=', $player->address()?->id)
                    ->delete();

                $player->addresses()
                    ->email()
                    ->where('addresses.id', '!=', $player->emailAddress()?->id)
                    ->delete();
            },
        );
    }
}

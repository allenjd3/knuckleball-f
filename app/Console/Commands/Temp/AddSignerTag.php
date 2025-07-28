<?php

namespace App\Console\Commands\Temp;

use App\Models\Player;
use App\Models\PlayerTag;
use App\Models\SignerTag;
use Illuminate\Console\Command;

use function Laravel\Prompts\progress;

class AddSignerTag extends Command
{
    protected $signature = 'operation:add-signer-tag';

    protected $description = 'One off command for adding the signer tag table';

    public function handle()
    {
        progress(
            label: 'Add signer_tag',
            steps: PlayerTag::all(),
            callback: function ($playerTag) {
                $signerId = Player::find($playerTag->player_id)->signer->id;

                SignerTag::create([
                    'signer_id' => $signerId,
                    'tag_id' => $playerTag->tag_id,
                    'user_id' => $playerTag->user_id,
                    'approved_at' => $playerTag->approved_at,
                ]);
            },
        );
    }
}

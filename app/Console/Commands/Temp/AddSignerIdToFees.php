<?php

namespace App\Console\Commands\Temp;

use App\Models\Fee;
use App\Models\Signer;
use Illuminate\Console\Command;

use function Laravel\Prompts\progress;

class AddSignerIdToFees extends Command
{
    protected $signature = 'operation:add-signer-id-to-fees';

    protected $description = 'One off command for adding the signer id to fees table';

    public function handle()
    {
        progress(
            label: 'Updated signer_id',
            steps: Fee::all(),
            callback: fn ($fee) => $fee->update([
                'signer_id' => Signer::where('signable_type', 'player')
                    ->where('signable_id', $fee->player_id)
                    ->first()
                    ?->id,
            ])
        );
    }
}

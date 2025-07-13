<?php

namespace App\Console\Commands\temp;

use App\Models\PostalMail;
use App\Models\Signer;
use Illuminate\Console\Command;

use function Laravel\Prompts\progress;

class AddSignerIdToPostalMails extends Command
{
    protected $signature = 'operation:add-signer-id-to-postal-mails';

    protected $description = 'One off command for adding the signer id to postal mails table';

    public function handle()
    {
        progress(
            label: 'Updated signer_id',
            steps: PostalMail::all(),
            callback: fn ($postalMail) => $postalMail->update([
                'signer_id' => Signer::where('signable_type', 'player')
                    ->where('signable_id', $postalMail->player_id)
                    ->first()
                    ->id,
            ])
        );
    }
}

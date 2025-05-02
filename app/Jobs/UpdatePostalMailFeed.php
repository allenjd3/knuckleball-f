<?php

namespace App\Jobs;

use App\Models\Feed;
use App\Models\PostalMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdatePostalMailFeed implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Feed::get()->each(function ($feed) {
            $mail = PostalMail::find($feed->feedable_id);
            $feed->update(['meta' => $mail->generateMeta()]);
        });
    }
}

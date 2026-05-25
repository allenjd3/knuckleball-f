<?php

namespace App\Jobs;

use App\Models\PostalMail;
use App\Services\ReturnCardService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateReturnCard implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $postalMailId) {}

    public function handle(ReturnCardService $service): void
    {
        $mail = PostalMail::find($this->postalMailId);

        if (! $mail) {
            return;
        }

        $service->generate($mail);
    }
}

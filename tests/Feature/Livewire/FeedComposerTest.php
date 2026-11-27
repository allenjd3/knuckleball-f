<?php

use App\Jobs\GenerateReturnCard;
use App\Livewire\FeedComposer;
use App\Models\PostalMail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

it('queues one share card when logging a return', function () {
    $postalMail = PostalMail::factory()->unReturned()->create();
    Queue::fake();

    Livewire::actingAs($postalMail->user)
        ->test(FeedComposer::class)
        ->callAction('logReturn', [
            'postal_mail_id' => $postalMail->id,
            'returned_date' => now()->format('Y-m-d'),
        ])
        ->assertHasNoActionErrors();

    expect($postalMail->fresh()->returned_date)->not->toBeNull();

    Queue::assertPushedTimes(GenerateReturnCard::class, 1);
});

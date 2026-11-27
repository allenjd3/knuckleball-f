<?php

use App\Actions\CreateFeedItem;
use App\Jobs\GenerateReturnCard;
use App\Models\NoCard;
use App\Models\PostalMail;
use Illuminate\Support\Facades\Queue;

test('it includes a default card if it doesnt have one', function () {
    PostalMail::factory()->create();

    expect(PostalMail::first()->card)->toBeInstanceOf(NoCard::class);
});

it('can be marked as failed', function () {
    $postalMail = PostalMail::factory()->create();
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    $this->assertFalse($postalMail->is_failed);

    $postalMail->fail();

    $this->assertTrue($postalMail->is_failed);
});

it('includes whether it failed in its feed meta', function (bool $isFailed) {
    $postalMail = PostalMail::factory()->create(['is_failed' => $isFailed]);

    expect($postalMail->generateMeta())->toHaveKey('is_failed', $isFailed);
})->with([
    'failed' => true,
    'not failed' => false,
]);

it('rebuilds the share card when a returned postal mail changes', function (array $changes) {
    $postalMail = PostalMail::factory()->returned()->create();
    Queue::fake();

    $postalMail->update($changes);

    Queue::assertPushed(GenerateReturnCard::class, fn (GenerateReturnCard $job): bool => $job->postalMailId === $postalMail->id);
})->with([
    'marked failed' => [['is_failed' => true]],
    'returned date changed' => [['returned_date' => now()->subDays(3)]],
    'sent date changed' => [['date_sent' => now()->subWeeks(3)]],
]);

it('rebuilds the share card when a returned postal mail is failed', function () {
    $postalMail = PostalMail::factory()->returned()->create();
    Queue::fake();

    $postalMail->fail();

    Queue::assertPushed(GenerateReturnCard::class);
});

it('does not rebuild the share card for unrelated changes or unreturned mail', function () {
    $returned = PostalMail::factory()->returned()->create();
    $pending = PostalMail::factory()->unReturned()->create();
    Queue::fake();

    $returned->update(['comment' => 'new comment']);
    $pending->update(['is_failed' => true]);

    Queue::assertNotPushed(GenerateReturnCard::class);
});

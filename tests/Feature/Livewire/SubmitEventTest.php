<?php

use App\Livewire\SubmitEvent;
use App\Models\Event;
use App\Models\Player;
use App\Models\User;
use Livewire\Livewire;

// Re-uses the helper defined in ShowEventTest
function fakeStripePaymentIntentForSubmit(string $status, string $customer, string $intentId = 'pi_test_123'): void
{
    config(['cashier.secret' => 'sk_test_fake_key_for_testing']);

    $body = json_encode([
        'id'       => $intentId,
        'object'   => 'payment_intent',
        'status'   => $status,
        'customer' => $customer,
        'amount'   => 999,
        'currency' => 'usd',
        'livemode' => false,
        'created'  => time(),
    ]);

    $mock = new class ($body) implements \Stripe\HttpClient\ClientInterface {
        public function __construct(private string $body) {}

        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
        {
            return [$this->body, 200, ['Request-Id' => 'req_test']];
        }
    };

    \Stripe\ApiRequestor::setHttpClient($mock);
}

// ── confirmFeaturePayment ─────────────────────────────────────────────────

test('confirmFeaturePayment creates a FeaturedListing when customer matches', function () {
    $user  = User::factory()->create(['stripe_id' => 'cus_correct_456']);
    $event = Event::factory()->approved()->for($user)->create();

    fakeStripePaymentIntentForSubmit('succeeded', 'cus_correct_456');

    Livewire::actingAs($user)
        ->test(SubmitEvent::class)
        ->set('submittedEventId', $event->id)
        ->set('type', $event->type)
        ->call('confirmFeaturePayment', 'pi_test_123');

    $this->assertDatabaseHas('featured_listings', [
        'event_id'                 => $event->id,
        'stripe_payment_intent_id' => 'pi_test_123',
    ]);
});

test('confirmFeaturePayment rejects a PaymentIntent from a different customer', function () {
    $user  = User::factory()->create(['stripe_id' => 'cus_user_a']);
    $event = Event::factory()->approved()->for($user)->create();

    fakeStripePaymentIntentForSubmit('succeeded', 'cus_user_b');

    Livewire::actingAs($user)
        ->test(SubmitEvent::class)
        ->set('submittedEventId', $event->id)
        ->set('type', $event->type)
        ->call('confirmFeaturePayment', 'pi_test_123');

    $this->assertDatabaseMissing('featured_listings', ['event_id' => $event->id]);
});

// ── validation ───────────────────────────────────────────────────────────

test('submit validation rejects non-existent player IDs in expected_signer_ids', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(SubmitEvent::class)
        ->set('type', 'card_show')
        ->set('name', 'Big Card Show')
        ->set('start_date', now()->addWeek()->toDateString())
        ->set('expected_signer_ids', [99999])
        ->call('submit')
        ->assertHasErrors('expected_signer_ids.*');
});

test('submit validation accepts valid player IDs in expected_signer_ids', function () {
    $user   = User::factory()->create();
    $player = Player::factory()->create();

    Livewire::actingAs($user)
        ->test(SubmitEvent::class)
        ->set('type', 'card_show')
        ->set('name', 'Big Card Show')
        ->set('start_date', now()->addWeek()->toDateString())
        ->set('expected_signer_ids', [$player->id])
        ->call('submit')
        ->assertHasNoErrors('expected_signer_ids.*');
});

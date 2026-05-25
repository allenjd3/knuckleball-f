<?php

use App\Livewire\ShowEvent;
use App\Models\Event;
use App\Models\User;
use Livewire\Livewire;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;

// Helper: mock the Stripe HTTP layer to return a fake PaymentIntent
function fakeStripePaymentIntent(string $status, string $customer, string $intentId = 'pi_test_123'): void
{
    // Provide a fake key so Cashier::stripe() doesn't throw before reaching the mock.
    config(['cashier.secret' => 'sk_test_fake_key_for_testing']);

    $body = json_encode([
        'id' => $intentId,
        'object' => 'payment_intent',
        'status' => $status,
        'customer' => $customer,
        'amount' => 999,
        'currency' => 'usd',
        'livemode' => false,
        'created' => time(),
    ]);

    $mock = new class($body) implements ClientInterface
    {
        public function __construct(private string $body) {}

        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
        {
            return [$this->body, 200, ['Request-Id' => 'req_test']];
        }
    };

    ApiRequestor::setHttpClient($mock);
}

// ── visibility ────────────────────────────────────────────────────────────

test('guests cannot view an unapproved event', function () {
    $event = Event::factory()->create(['status' => 'pending']);

    Livewire::test(ShowEvent::class, ['event' => $event])
        ->assertStatus(404);
});

test('the event owner can view their own unapproved event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create(['status' => 'pending']);

    Livewire::actingAs($user)
        ->test(ShowEvent::class, ['event' => $event])
        ->assertStatus(200);
});

test('an admin can view any unapproved event', function () {
    $admin = User::factory()->isSuperAdmin()->create();
    $event = Event::factory()->create(['status' => 'pending']);

    Livewire::actingAs($admin)
        ->test(ShowEvent::class, ['event' => $event])
        ->assertStatus(200);
});

test('an approved event is visible to all', function () {
    $event = Event::factory()->approved()->create();

    Livewire::test(ShowEvent::class, ['event' => $event])
        ->assertStatus(200);
});

// ── confirmPayment ────────────────────────────────────────────────────────

test('confirmPayment creates a FeaturedListing when customer matches', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_correct_123']);
    $event = Event::factory()->approved()->for($user)->create();

    fakeStripePaymentIntent('succeeded', 'cus_correct_123');

    Livewire::actingAs($user)
        ->test(ShowEvent::class, ['event' => $event])
        ->call('confirmPayment', 'pi_test_123');

    $this->assertDatabaseHas('featured_listings', [
        'event_id' => $event->id,
        'user_id' => $user->id,
        'stripe_payment_intent_id' => 'pi_test_123',
    ]);

    expect($event->fresh()->is_featured)->toBeTrue();
});

test('confirmPayment rejects a PaymentIntent belonging to a different customer', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_user_a']);
    $event = Event::factory()->approved()->for($user)->create();

    fakeStripePaymentIntent('succeeded', 'cus_user_b');

    Livewire::actingAs($user)
        ->test(ShowEvent::class, ['event' => $event])
        ->call('confirmPayment', 'pi_test_123');

    $this->assertDatabaseMissing('featured_listings', ['event_id' => $event->id]);
    expect($event->fresh()->is_featured)->toBeFalse();
});

test('confirmPayment does nothing when the PaymentIntent status is not succeeded', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_correct_123']);
    $event = Event::factory()->approved()->for($user)->create();

    fakeStripePaymentIntent('requires_payment_method', 'cus_correct_123');

    Livewire::actingAs($user)
        ->test(ShowEvent::class, ['event' => $event])
        ->call('confirmPayment', 'pi_test_123');

    $this->assertDatabaseMissing('featured_listings', ['event_id' => $event->id]);
});

<?php

use App\Models\Event;
use App\Models\Player;
use Illuminate\Support\Facades\Http;

function signingJsonLd(array $overrides = []): array
{
    return array_merge([
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => 'Johnny Bench Autograph Signing',
        'startDate' => '2026-07-29',
        'endDate' => '2026-08-02',
        'location' => [
            'name' => 'Donald E. Stephens Convention Center',
            'address' => [
                'streetAddress' => '5555 N River Rd',
                'addressLocality' => 'Rosemont',
                'addressRegion' => 'IL',
                'postalCode' => '60018',
            ],
        ],
        'performer' => ['name' => 'Johnny Bench'],
        'organizer' => ['name' => 'TRISTAR Productions'],
        'offers' => ['price' => '75', 'priceCurrency' => 'USD'],
    ], $overrides);
}

function fakeEventbritePage(array $jsonLd): string
{
    $encoded = json_encode($jsonLd);

    return "<html><head><script type=\"application/ld+json\">{$encoded}</script></head><body>Event page</body></html>";
}

test('it imports signings from a json-ld source', function () {
    Http::fake([
        'example.com/*' => Http::response(fakeEventbritePage(signingJsonLd())),
    ]);

    $this->artisan('signings:scrape --source="tristar:https://example.com/events"')
        ->assertSuccessful();

    expect(Event::where('name', 'Johnny Bench Autograph Signing')->exists())->toBeTrue();

    $event = Event::where('name', 'Johnny Bench Autograph Signing')->first();
    expect($event->status)->toBe('pending')
        ->and($event->city)->toBe('Rosemont')
        ->and($event->state)->toBe('IL')
        ->and($event->promoter_name)->toBe('TRISTAR Productions')
        ->and($event->is_multi_day)->toBeTrue();
});

test('it skips signings already imported by source_hash', function () {
    Http::fake([
        'example.com/*' => Http::response(fakeEventbritePage(signingJsonLd())),
    ]);

    $this->artisan('signings:scrape --source="tristar:https://example.com/events"')->assertSuccessful();
    $countAfterFirst = Event::count();

    $this->artisan('signings:scrape --source="tristar:https://example.com/events"')->assertSuccessful();

    expect(Event::count())->toBe($countAfterFirst);
});

test('it resolves a matching player by name', function () {
    $player = Player::factory()->create(['name' => 'Johnny Bench']);

    Http::fake([
        'example.com/*' => Http::response(fakeEventbritePage(signingJsonLd())),
    ]);

    $this->artisan('signings:scrape --source="tristar:https://example.com/events"')->assertSuccessful();

    expect(Event::first()->player_id)->toBe($player->id);
});

test('it puts unresolved player name in notes', function () {
    Http::fake([
        'example.com/*' => Http::response(fakeEventbritePage(signingJsonLd())),
    ]);

    $this->artisan('signings:scrape --source="tristar:https://example.com/events"')->assertSuccessful();

    $event = Event::first();
    expect($event->notes)->toContain('Johnny Bench')
        ->and($event->notes)->toContain('no match found');
});

test('it puts admission info in notes', function () {
    Http::fake([
        'example.com/*' => Http::response(fakeEventbritePage(signingJsonLd())),
    ]);

    $this->artisan('signings:scrape --source="tristar:https://example.com/events"')->assertSuccessful();

    expect(Event::first()->notes)->toContain('75 USD');
});

test('it detects mail-in format from event description', function () {
    Http::fake([
        'example.com/*' => Http::response(fakeEventbritePage(signingJsonLd([
            'name' => 'Mail-In Signing Event',
            'description' => 'Send in your items via mail-in submission.',
            'endDate' => '2026-07-29',
        ]))),
    ]);

    $this->artisan('signings:scrape --source="tristar:https://example.com/events"')->assertSuccessful();

    expect(Event::first()->event_subtype)->toBe('mail_in');
});

test('it exits cleanly with no sources provided', function () {
    $this->artisan('signings:scrape')
        ->assertSuccessful()
        ->expectsOutputToContain('No sources provided');

    expect(Event::count())->toBe(0);
});

test('dry run prints results without saving', function () {
    Http::fake([
        'example.com/*' => Http::response(fakeEventbritePage(signingJsonLd())),
    ]);

    $this->artisan('signings:scrape --dry-run --source="tristar:https://example.com/events"')
        ->assertSuccessful()
        ->expectsOutputToContain('Johnny Bench');

    expect(Event::count())->toBe(0);
});

<?php

use App\Models\CardShop;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.google_places.key' => 'fake-api-key']);
});

function placesResponse(array $places, ?string $nextPageToken = null): array
{
    return array_filter([
        'places' => $places,
        'nextPageToken' => $nextPageToken,
    ]);
}

function fakePlaceData(array $overrides = []): array
{
    return array_merge([
        'id' => 'ChIJ_fake_' . fake()->uuid(),
        'displayName' => ['text' => 'Cincinnati Sports Cards'],
        'formattedAddress' => '123 Main St, Cincinnati, OH 45202',
        'addressComponents' => [
            ['longText' => 'Cincinnati', 'types' => ['locality']],
            ['shortText' => 'OH', 'types' => ['administrative_area_level_1']],
            ['longText' => '45202', 'types' => ['postal_code']],
            ['shortText' => 'US', 'types' => ['country']],
        ],
        'location' => ['latitude' => 39.1031, 'longitude' => -84.5120],
        'nationalPhoneNumber' => '(513) 555-0100',
        'businessStatus' => 'OPERATIONAL',
        'types' => ['store', 'point_of_interest'],
        'rating' => 4.5,
        'userRatingCount' => 120,
    ], $overrides);
}

test('it imports a new card shop as pending', function () {
    Http::fake([
        'places.googleapis.com/*' => Http::response(placesResponse([fakePlaceData()])),
    ]);

    $this->artisan('shops:fetch --metros="Cincinnati, OH"')->assertSuccessful();

    expect(CardShop::where('name', 'Cincinnati Sports Cards')->exists())->toBeTrue();

    $shop = CardShop::where('name', 'Cincinnati Sports Cards')->first();
    expect($shop->status)->toBe('pending')
        ->and($shop->city)->toBe('Cincinnati')
        ->and($shop->state)->toBe('OH')
        ->and($shop->latitude)->toBe('39.1031000')
        ->and($shop->place_id)->toStartWith('ChIJ_fake_');
});

test('it skips a shop that already exists by place_id', function () {
    $place = fakePlaceData();
    CardShop::factory()->create(['place_id' => $place['id'], 'city' => 'Cincinnati', 'state' => 'OH']);

    Http::fake([
        'places.googleapis.com/*' => Http::response(placesResponse([$place])),
    ]);

    $this->artisan('shops:fetch --metros="Cincinnati, OH"')->assertSuccessful();

    expect(CardShop::where('place_id', $place['id'])->count())->toBe(1);
});

test('it filters out permanently closed shops', function () {
    Http::fake([
        'places.googleapis.com/*' => Http::response(placesResponse([
            fakePlaceData(['displayName' => ['text' => 'Closed Cards'], 'businessStatus' => 'CLOSED_PERMANENTLY']),
        ])),
    ]);

    $this->artisan('shops:fetch --metros="Cincinnati, OH"')->assertSuccessful();

    expect(CardShop::where('name', 'Closed Cards')->exists())->toBeFalse();
});

test('it filters out non-card-shop businesses by name', function () {
    Http::fake([
        'places.googleapis.com/*' => Http::response(placesResponse([
            fakePlaceData(['displayName' => ['text' => 'Rosie\'s Gift Shop']]),
        ])),
    ]);

    $this->artisan('shops:fetch --metros="Cincinnati, OH"')->assertSuccessful();

    expect(CardShop::where('name', 'Rosie\'s Gift Shop')->exists())->toBeFalse();
});

test('it accepts a hobby shop by type even without keywords in the name', function () {
    Http::fake([
        'places.googleapis.com/*' => Http::response(placesResponse([
            fakePlaceData([
                'displayName' => ['text' => 'Bob\'s Emporium'],
                'types' => ['hobby_shop', 'store'],
            ]),
        ])),
    ]);

    $this->artisan('shops:fetch --metros="Cincinnati, OH"')->assertSuccessful();

    expect(CardShop::where('name', 'Bob\'s Emporium')->exists())->toBeTrue();
});

test('it skips shops missing city or state', function () {
    Http::fake([
        'places.googleapis.com/*' => Http::response(placesResponse([
            fakePlaceData([
                'displayName' => ['text' => 'Mystery Cards'],
                'addressComponents' => [],
            ]),
        ])),
    ]);

    $this->artisan('shops:fetch --metros="Cincinnati, OH"')->assertSuccessful();

    expect(CardShop::where('name', 'Mystery Cards')->exists())->toBeFalse();
});

test('dry run prints results without saving', function () {
    Http::fake([
        'places.googleapis.com/*' => Http::response(placesResponse([fakePlaceData()])),
    ]);

    $this->artisan('shops:fetch --dry-run --metros="Cincinnati, OH"')
        ->assertSuccessful()
        ->expectsOutputToContain('Cincinnati Sports Cards');

    expect(CardShop::count())->toBe(0);
});

test('verify marks permanently closed approved shops as rejected', function () {
    $shop = CardShop::factory()->approved()->create([
        'place_id' => 'ChIJtest123',
        'city' => 'Cincinnati',
        'state' => 'OH',
    ]);

    Http::fake([
        'places.googleapis.com/v1/places/ChIJtest123' => Http::response([
            'id' => 'ChIJtest123',
            'businessStatus' => 'CLOSED_PERMANENTLY',
        ]),
    ]);

    $this->artisan('shops:fetch --verify')->assertSuccessful();

    expect($shop->fresh()->status)->toBe('rejected')
        ->and($shop->fresh()->rejection_reason)->toContain('Permanently closed');
});

test('fails without a google places api key', function () {
    config(['services.google_places.key' => null]);

    $this->artisan('shops:fetch')->assertFailed();
});

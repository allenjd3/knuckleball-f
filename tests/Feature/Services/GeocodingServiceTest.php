<?php

use App\Services\GeocodingService;
use Illuminate\Support\Facades\Http;

test('it returns coordinates for a valid address', function () {
    Http::fake([
        'maps.googleapis.com/*' => Http::response([
            'status'  => 'OK',
            'results' => [
                ['geometry' => ['location' => ['lat' => 40.7128, 'lng' => -74.0060]]],
            ],
        ]),
    ]);

    config(['services.google_maps.key' => 'test-key']);

    $result = app(GeocodingService::class)->geocode('New York, NY');

    expect($result)->toBe(['latitude' => 40.7128, 'longitude' => -74.0060]);
});

test('it returns null when no API key is configured', function () {
    config(['services.google_maps.key' => null]);

    $result = app(GeocodingService::class)->geocode('New York, NY');

    expect($result)->toBeNull();
});

test('it returns null when the Google API returns a non-OK status', function () {
    Http::fake([
        'maps.googleapis.com/*' => Http::response(['status' => 'ZERO_RESULTS', 'results' => []]),
    ]);

    config(['services.google_maps.key' => 'test-key']);

    $result = app(GeocodingService::class)->geocode('00000');

    expect($result)->toBeNull();
});

test('geocodeZip caches results and calls the API only once for the same zip', function () {
    Http::fake([
        'maps.googleapis.com/*' => Http::response([
            'status'  => 'OK',
            'results' => [
                ['geometry' => ['location' => ['lat' => 40.7128, 'lng' => -74.0060]]],
            ],
        ]),
    ]);

    config(['services.google_maps.key' => 'test-key']);

    $service = app(GeocodingService::class);

    $service->geocodeZip('10001', 'US');
    $service->geocodeZip('10001', 'US');

    Http::assertSentCount(1);
});

test('geocodeZip makes separate API calls for different zips', function () {
    Http::fake([
        'maps.googleapis.com/*' => Http::response([
            'status'  => 'OK',
            'results' => [
                ['geometry' => ['location' => ['lat' => 40.7128, 'lng' => -74.0060]]],
            ],
        ]),
    ]);

    config(['services.google_maps.key' => 'test-key']);

    $service = app(GeocodingService::class);

    $service->geocodeZip('10001', 'US');
    $service->geocodeZip('90001', 'US');

    Http::assertSentCount(2);
});

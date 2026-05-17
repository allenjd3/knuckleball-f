<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    public function geocode(string $address): ?array
    {
        $key = config('services.google_maps.key');

        if (! $key) {
            return null;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key'     => $key,
            ]);

            $data = $response->json();

            if ($data['status'] !== 'OK' || empty($data['results'])) {
                return null;
            }

            $location = $data['results'][0]['geometry']['location'];

            return [
                'latitude'  => $location['lat'],
                'longitude' => $location['lng'],
            ];
        } catch (\Throwable $e) {
            Log::warning('Geocoding failed', ['address' => $address, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function geocodeZip(string $zip, string $country = 'US'): ?array
    {
        return Cache::remember("geo_{$zip}_{$country}", 86400, fn () => $this->geocode("{$zip}, {$country}"));
    }
}

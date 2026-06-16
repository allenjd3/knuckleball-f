<?php

namespace App\Console\Commands;

use App\Models\CardShop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchCardShops extends Command
{
    private const SEARCH_URL = 'https://places.googleapis.com/v1/places:searchText';

    private const DETAILS_URL = 'https://places.googleapis.com/v1/places/';

    private const FIELD_MASK = 'places.id,places.displayName,places.formattedAddress,places.addressComponents,places.location,places.nationalPhoneNumber,places.websiteUri,places.regularOpeningHours.weekdayDescriptions,places.businessStatus,places.rating,places.userRatingCount,places.types,nextPageToken';

    private const SEARCH_TERMS = [
        'sports card shop',
        'baseball card store',
        'trading card hobby shop',
    ];

    private const DEFAULT_METROS = [
        'Cincinnati, OH',
        'Dayton, OH',
        'Columbus, OH',
        'Lynchburg, VA',
        'Richmond, VA',
        'Charlotte, NC',
    ];

    private const CARD_SHOP_KEYWORDS = '/\b(card|cards|collectible|collectibles|hobby|memorabilia|comic|autograph|baseball|sports)\b/i';

    private const BLOCKLIST = '/\b(gift|book|antique|thrift|toy|game store|candy|floral|florist|pharmacy|salon|spa|restaurant|cafe|coffee|pizza|burger|sushi|grocery|hardware|clothing|jewelry|furniture|pet|auto|insurance)\b/i';

    protected $signature = 'shops:fetch
                            {--dry-run : Print results without saving}
                            {--metros=* : Override the default metro list}
                            {--verify : Re-check approved shops for permanent closures}';

    protected $description = 'Fetch sports card shops from Google Places and import as pending';

    public function handle(): int
    {
        $apiKey = config('services.google_places.key');

        if (! $apiKey) {
            $this->error('Missing GOOGLE_PLACES_API_KEY. Set it in your .env and config/services.php.');

            return self::FAILURE;
        }

        if ($this->option('verify')) {
            return $this->runVerify($apiKey);
        }

        $metros = $this->option('metros') ?: self::DEFAULT_METROS;
        $shops = $this->discover($apiKey, $metros);

        $this->info('Total unique shops found: ' . count($shops));

        if ($this->option('dry-run')) {
            $this->table(
                ['Place ID', 'Name', 'City', 'ST', 'Rating', 'Phone'],
                array_map(fn ($s) => [
                    $s['place_id'],
                    mb_strimwidth($s['name'], 0, 35, '…'),
                    $s['city'] ?? '—',
                    $s['state'] ?? '—',
                    $s['rating'] ? "{$s['rating']} ({$s['rating_count']})" : '—',
                    $s['phone'] ?? '—',
                ], $shops)
            );

            return self::SUCCESS;
        }

        [$created, $skipped] = $this->import($shops);

        $this->info("Imported: {$created} created, {$skipped} already exist.");
        Log::info("shops:fetch — {$created} created, {$skipped} skipped");

        return self::SUCCESS;
    }

    private function discover(string $apiKey, array $metros): array
    {
        $all = [];

        foreach ($metros as $metro) {
            $countBefore = count($all);

            foreach (self::SEARCH_TERMS as $term) {
                foreach ($this->searchMetro($apiKey, $term, $metro) as $shop) {
                    $all[$shop['place_id']] = $shop;
                }
            }

            $added = count($all) - $countBefore;
            $this->line("{$metro}: +{$added} shops (" . count($all) . ' total)');
        }

        return array_values($all);
    }

    private function searchMetro(string $apiKey, string $term, string $metro): array
    {
        $shops = [];
        $pageToken = null;
        $headers = [
            'X-Goog-Api-Key' => $apiKey,
            'X-Goog-FieldMask' => self::FIELD_MASK,
        ];

        do {
            $body = ['textQuery' => "{$term} in {$metro}"];
            if ($pageToken) {
                $body['pageToken'] = $pageToken;
            }

            sleep(1);

            $response = Http::withHeaders($headers)->post(self::SEARCH_URL, $body);

            if (! $response->ok()) {
                $this->warn("Places search failed ({$response->status()}) for '{$term} in {$metro}'");
                break;
            }

            $data = $response->json();

            foreach ($data['places'] ?? [] as $place) {
                if (($place['businessStatus'] ?? '') === 'CLOSED_PERMANENTLY') {
                    continue;
                }

                $name = $place['displayName']['text'] ?? '';
                $types = $place['types'] ?? [];

                if (! $this->isCardShop($name, $types)) {
                    continue;
                }

                $shops[] = $this->parsePlace($place);
            }

            $pageToken = $data['nextPageToken'] ?? null;
        } while ($pageToken);

        return $shops;
    }

    private function isCardShop(string $name, array $types): bool
    {
        if (preg_match(self::BLOCKLIST, $name)) {
            return false;
        }

        if (preg_match(self::CARD_SHOP_KEYWORDS, $name)) {
            return true;
        }

        return in_array('hobby_shop', $types);
    }

    private function parsePlace(array $place): array
    {
        $components = $this->parseAddressComponents($place['addressComponents'] ?? []);
        $loc = $place['location'] ?? [];

        return [
            'place_id' => $place['id'],
            'name' => $place['displayName']['text'] ?? 'Unknown',
            'address' => $place['formattedAddress'] ?? null,
            'city' => $components['city'],
            'state' => $components['state'],
            'zip_code' => $components['zip_code'],
            'country' => $components['country'],
            'lat' => $loc['latitude'] ?? null,
            'lng' => $loc['longitude'] ?? null,
            'phone' => $place['nationalPhoneNumber'] ?? null,
            'website' => $place['websiteUri'] ?? null,
            'hours' => $place['regularOpeningHours']['weekdayDescriptions'] ?? null,
            'rating' => $place['rating'] ?? null,
            'rating_count' => $place['userRatingCount'] ?? null,
        ];
    }

    private function parseAddressComponents(array $components): array
    {
        $out = ['city' => null, 'state' => null, 'zip_code' => null, 'country' => null];

        foreach ($components as $c) {
            $types = $c['types'] ?? [];
            if (in_array('locality', $types)) {
                $out['city'] = $c['longText'] ?? null;
            } elseif (in_array('administrative_area_level_1', $types)) {
                $out['state'] = $c['shortText'] ?? null;
            } elseif (in_array('postal_code', $types)) {
                $out['zip_code'] = $c['longText'] ?? null;
            } elseif (in_array('country', $types)) {
                $out['country'] = $c['shortText'] ?? null;
            }
        }

        return $out;
    }

    /** @return array{int, int} */
    private function import(array $shops): array
    {
        $created = $skipped = 0;

        foreach ($shops as $shop) {
            if (CardShop::where('place_id', $shop['place_id'])->exists()) {
                $skipped++;

                continue;
            }

            if (! $shop['city'] || ! $shop['state']) {
                $this->line("Skipping '{$shop['name']}' — missing city or state.");

                continue;
            }

            CardShop::create([
                'place_id' => $shop['place_id'],
                'name' => $shop['name'],
                'address' => $shop['address'],
                'city' => $shop['city'],
                'state' => $shop['state'],
                'zip_code' => $shop['zip_code'],
                'country' => $shop['country'] ?? 'US',
                'latitude' => $shop['lat'],
                'longitude' => $shop['lng'],
                'phone' => $shop['phone'],
                'website' => $shop['website'],
                'hours' => $shop['hours'],
                'status' => 'pending',
            ]);

            $created++;
        }

        return [$created, $skipped];
    }

    private function runVerify(string $apiKey): int
    {
        $shops = CardShop::where('status', 'approved')
            ->whereNotNull('place_id')
            ->get();

        $this->info("Verifying {$shops->count()} shops...");

        $closedCount = 0;

        foreach ($shops as $shop) {
            sleep(1);

            $response = Http::withHeaders([
                'X-Goog-Api-Key' => $apiKey,
                'X-Goog-FieldMask' => 'id,businessStatus',
            ])->get(self::DETAILS_URL . $shop->place_id);

            if ($response->ok() && $response->json('businessStatus') === 'CLOSED_PERMANENTLY') {
                $shop->update([
                    'status' => 'rejected',
                    'rejection_reason' => 'Permanently closed per Google Places verification',
                ]);
                $this->line("CLOSED: {$shop->name} ({$shop->place_id})");
                $closedCount++;
            }
        }

        $this->info("Verify complete: {$closedCount} closures found.");
        Log::info("shops:fetch --verify — {$closedCount} closures flagged");

        return self::SUCCESS;
    }
}

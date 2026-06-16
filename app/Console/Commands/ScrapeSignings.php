<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Player;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapeSignings extends Command
{
    private const USER_AGENT = 'KnuckleballEventBot/1.0 (+https://knuckleball.app/about)';

    protected $signature = 'signings:scrape
                            {--source=* : Source in name:url format (repeatable)}
                            {--dry-run : Print results without saving}';

    protected $description = 'Scrape player signing events and import as pending';

    public function handle(): int
    {
        $sources = $this->parseSources();

        if (empty($sources)) {
            $this->warn('No sources provided. Use --source="name:https://example.com/events"');

            return self::SUCCESS;
        }

        $seen = [];
        $allSignings = [];

        foreach ($sources as [$name, $url]) {
            foreach ($this->jsonLdSource($name, [$url]) as $signing) {
                $hash = $this->dedupeHash($signing);
                if (isset($seen[$hash])) {
                    continue;
                }
                $seen[$hash] = true;
                $signing['source_hash'] = $hash;
                $allSignings[] = $signing;
            }
        }

        $this->info('Total unique signings: ' . count($allSignings));

        if ($this->option('dry-run')) {
            $this->table(
                ['Title', 'Date', 'Player', 'City', 'ST', 'Format'],
                array_map(fn ($s) => [
                    mb_strimwidth($s['title'], 0, 40, '…'),
                    $s['start_date'],
                    $s['player_name'] ?? '—',
                    $s['city'] ?? '—',
                    $s['state'] ?? '—',
                    $s['event_format'],
                ], $allSignings)
            );

            return self::SUCCESS;
        }

        [$created, $skipped] = $this->import($allSignings);

        $this->info("Imported: {$created} created, {$skipped} already exist.");
        Log::info("signings:scrape — {$created} created, {$skipped} skipped");

        return self::SUCCESS;
    }

    /** @return array{int, int} */
    private function import(array $signings): array
    {
        $created = $skipped = 0;

        foreach ($signings as $signing) {
            if (Event::where('source_hash', $signing['source_hash'])->exists()) {
                $skipped++;

                continue;
            }

            $playerId = $this->resolvePlayer($signing['player_name'] ?? null);

            Event::create([
                'type' => $signing['event_type'],
                'name' => $signing['title'],
                'player_id' => $playerId,
                'event_subtype' => $signing['event_format'],
                'is_multi_day' => isset($signing['end_date']) && $signing['end_date'] !== $signing['start_date'],
                'start_date' => $signing['start_date'],
                'end_date' => $signing['end_date'] ?? null,
                'venue_name' => $signing['venue'] ?? null,
                'address' => $signing['address'] ?? null,
                'city' => $signing['city'] ?? null,
                'state' => $signing['state'] ?? null,
                'zip_code' => $signing['zip_code'] ?? null,
                'promoter_name' => $signing['promoter'] ?? null,
                'notes' => $this->buildNotes($signing, $playerId),
                'source_hash' => $signing['source_hash'],
                'status' => 'pending',
            ]);

            $created++;
        }

        return [$created, $skipped];
    }

    private function resolvePlayer(?string $name): ?int
    {
        if (! $name) {
            return null;
        }

        return Player::where('name', $name)->value('id');
    }

    private function buildNotes(array $signing, ?int $resolvedPlayerId): ?string
    {
        $lines = [];

        if (($signing['player_name'] ?? null) && ! $resolvedPlayerId) {
            $lines[] = "Player: {$signing['player_name']} (no match found in players table)";
        }

        if ($signing['admission'] ?? null) {
            $lines[] = "Admission: {$signing['admission']}";
        }

        if ($signing['contact'] ?? null) {
            $lines[] = "Contact: {$signing['contact']}";
        }

        if ($signing['source_url'] ?? null) {
            $lines[] = "Source: {$signing['source_url']} ({$signing['source_name']})";
        }

        return $lines ? implode("\n", $lines) : null;
    }

    private function dedupeHash(array $signing): string
    {
        $key = implode('|', [
            preg_replace('/\W+/', '', strtolower($signing['title'])),
            $signing['start_date'],
            strtolower(trim($signing['zip_code'] ?? $signing['city'] ?? '')),
        ]);

        return substr(hash('sha256', $key), 0, 16);
    }

    // -------------------------------------------------------------------------
    // Sources
    // -------------------------------------------------------------------------

    /** @return array<int, array{string, string}> */
    private function parseSources(): array
    {
        return collect($this->option('source'))
            ->map(fn ($s) => explode(':', $s, 2))
            ->filter(fn ($parts) => count($parts) === 2)
            ->values()
            ->all();
    }

    private function jsonLdSource(string $sourceName, array $urls): array
    {
        $signings = [];

        foreach ($urls as $url) {
            sleep(2);

            $response = Http::withUserAgent(self::USER_AGENT)->get($url);

            if (! $response->ok()) {
                $this->warn("Fetch failed for {$url} ({$response->status()})");

                continue;
            }

            $found = $this->extractJsonLdSignings($response->body(), $url, $sourceName);
            $this->line("{$sourceName}: " . count($found) . " signings from {$url}");
            $signings = array_merge($signings, $found);
        }

        return $signings;
    }

    private function extractJsonLdSignings(string $html, string $sourceUrl, string $sourceName): array
    {
        $dom = new DOMDocument;
        @$dom->loadHTML($html, LIBXML_NOERROR);
        $xpath = new DOMXPath($dom);
        $scripts = $xpath->query('//script[@type="application/ld+json"]');

        $signings = [];

        foreach ($scripts as $script) {
            $data = json_decode($script->nodeValue ?? '', true);
            if (! $data) {
                continue;
            }

            foreach ($this->walkForEvents($data) as $event) {
                $signing = $this->parseJsonLdEvent($event, $sourceUrl, $sourceName);
                if ($signing) {
                    $signings[] = $signing;
                }
            }
        }

        return $signings;
    }

    private function walkForEvents(mixed $node): array
    {
        $found = [];

        if (is_array($node)) {
            $type = $node['@type'] ?? '';
            $types = is_array($type) ? $type : [$type];

            if (array_filter($types, fn ($t) => str_contains((string) $t, 'Event'))) {
                $found[] = $node;
            }

            foreach ($node as $value) {
                $found = array_merge($found, $this->walkForEvents($value));
            }
        }

        return $found;
    }

    private function parseJsonLdEvent(array $event, string $sourceUrl, string $sourceName): ?array
    {
        $title = $event['name'] ?? '';
        $start = $this->isoDate((string) ($event['startDate'] ?? ''));

        if (! $title || ! $start) {
            return null;
        }

        $end = $this->isoDate((string) ($event['endDate'] ?? ''));
        if ($end === $start) {
            $end = null;
        }

        $location = is_array($event['location'] ?? null)
            ? (isset($event['location'][0]) ? $event['location'][0] : $event['location'])
            : [];

        $venue = $location['name'] ?? null;
        $addr = $location['address'] ?? [];
        $address = is_array($addr) ? ($addr['streetAddress'] ?? null) : $addr;
        $city = is_array($addr) ? ($addr['addressLocality'] ?? null) : null;
        $state = is_array($addr) ? ($addr['addressRegion'] ?? null) : null;
        $zipCode = is_array($addr) ? ($addr['postalCode'] ?? null) : null;

        $offers = is_array($event['offers'] ?? null)
            ? (isset($event['offers'][0]) ? $event['offers'][0] : $event['offers'])
            : [];
        $price = $offers['price'] ?? null;
        $admission = $price !== null && $price !== ''
            ? $price . ' ' . ($offers['priceCurrency'] ?? 'USD')
            : null;

        $organizer = is_array($event['organizer'] ?? null)
            ? (isset($event['organizer'][0]) ? $event['organizer'][0] : $event['organizer'])
            : [];
        $promoter = is_array($organizer) ? ($organizer['name'] ?? null) : null;

        $performer = is_array($event['performer'] ?? null)
            ? (isset($event['performer'][0]) ? $event['performer'][0] : $event['performer'])
            : [];
        $playerName = is_array($performer) ? ($performer['name'] ?? null) : null;

        $blob = $title . ' ' . ($event['description'] ?? '');
        $eventFormat = preg_match('/\b(mail[\s-]?in|send[\s-]?in|mail order)\b/i', $blob)
            ? 'mail_in'
            : 'in_person';

        return [
            'title' => trim($title),
            'start_date' => $start,
            'end_date' => $end,
            'player_name' => $playerName,
            'venue' => $venue,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zipCode,
            'admission' => $admission,
            'promoter' => $promoter,
            'contact' => null,
            'event_type' => 'player_signing',
            'event_format' => $eventFormat,
            'source_url' => $sourceUrl,
            'source_name' => $sourceName,
        ];
    }

    private function isoDate(string $value): ?string
    {
        preg_match('/(\d{4}-\d{2}-\d{2})/', $value, $m);

        return $m[1] ?? null;
    }
}

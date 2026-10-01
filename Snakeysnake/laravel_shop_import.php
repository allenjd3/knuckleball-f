<?php

/*
|--------------------------------------------------------------------------
| Knuckleball Shop Import — Laravel side (complete)
|--------------------------------------------------------------------------
| Mirrors the events pipeline. Pieces to split out:
|   1. Migration   -> database/migrations/xxxx_create_scraped_shops_table.php
|   2. Model       -> app/Models/ScrapedShop.php
|   3. Controller  -> app/Http/Controllers/Api/ShopImportController.php
|   4. Routes      -> routes/api.php (snippet)
|   5. Commands    -> app/Console/Commands/{ShopsReview,ShopsApprove,ShopsReject}.php
|
| Token: give your bot user a second ability or one combined token:
|   >>> $bot->createToken('scraper', ['events:import', 'shops:import'])->plainTextToken;
*/

// ===========================================================================
// 1. MIGRATION
// ===========================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraped_shops', function (Blueprint $table) {
            $table->id();
            $table->string('place_id')->unique();   // Google place_id = natural dedupe key
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('state', 2)->nullable()->index();
            $table->string('zip_code', 10)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->json('hours')->nullable();
            $table->decimal('rating', 2, 1)->nullable();
            $table->unsignedInteger('rating_count')->nullable();
            $table->string('business_status')->nullable();
            $table->string('source_name')->default('google_places');
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')->index();
            $table->foreignId('shop_id')->nullable()
                ->comment('Set when approved into the live shops table');
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraped_shops');
    }
};

// ===========================================================================
// 2. MODEL — app/Models/ScrapedShop.php
// ===========================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapedShop extends Model
{
    protected $fillable = [
        'place_id', 'name', 'address', 'city', 'state', 'zip_code',
        'lat', 'lng', 'phone', 'website', 'hours', 'rating',
        'rating_count', 'business_status', 'source_name',
        'status', 'shop_id', 'last_verified_at',
    ];

    protected $casts = [
        'hours' => 'array',
        'last_verified_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

// ===========================================================================
// 3. CONTROLLER — app/Http/Controllers/Api/ShopImportController.php
// ===========================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedShop;
use App\Models\Shop;            // your existing live Shop model
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopImportController extends Controller
{
    /** POST /api/shops/import — bulk import from the fetcher bot */
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->tokenCan('shops:import')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'shops' => ['required', 'array', 'max:200'],
            'shops.*.place_id' => ['required', 'string', 'max:255'],
            'shops.*.name' => ['required', 'string', 'max:255'],
            'shops.*.address' => ['nullable', 'string', 'max:255'],
            'shops.*.city' => ['nullable', 'string', 'max:255'],
            'shops.*.state' => ['nullable', 'string', 'size:2'],
            'shops.*.zip_code' => ['nullable', 'string', 'max:10'],
            'shops.*.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'shops.*.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'shops.*.phone' => ['nullable', 'string', 'max:30'],
            'shops.*.website' => ['nullable', 'url', 'max:255'],
            'shops.*.hours' => ['nullable', 'array'],
            'shops.*.rating' => ['nullable', 'numeric', 'between:0,5'],
            'shops.*.rating_count' => ['nullable', 'integer', 'min:0'],
            'shops.*.business_status' => ['nullable', 'string', 'max:50'],
            'shops.*.source_name' => ['nullable', 'string', 'max:100'],
        ]);

        $created = 0;
        $skipped = 0;

        foreach ($validated['shops'] as $shop) {
            if (ScrapedShop::where('place_id', $shop['place_id'])->exists()) {
                $skipped++;

                continue;
            }
            ScrapedShop::create($shop + ['status' => 'pending']);
            $created++;
        }

        Log::info("Shop import: {$created} created, {$skipped} skipped");

        return response()->json(['created' => $created, 'skipped' => $skipped], 201);
    }

    /** GET /api/shops/verify — list place_ids of live shops for the verify pass */
    public function verifyList(Request $request): JsonResponse
    {
        if (! $request->user()->tokenCan('shops:import')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $placeIds = ScrapedShop::where('status', 'approved')
            ->pluck('place_id');

        return response()->json(['place_ids' => $placeIds]);
    }

    /** POST /api/shops/verify — bot reports permanent closures */
    public function verifyReport(Request $request): JsonResponse
    {
        if (! $request->user()->tokenCan('shops:import')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'closed_place_ids' => ['required', 'array'],
            'closed_place_ids.*' => ['string'],
        ]);

        $flagged = 0;

        foreach ($validated['closed_place_ids'] as $placeId) {
            $scraped = ScrapedShop::where('place_id', $placeId)->first();
            if (! $scraped) {
                continue;
            }
            $scraped->update([
                'business_status' => 'CLOSED_PERMANENTLY',
                'last_verified_at' => now(),
            ]);
            // TODO: decide your policy — auto-unpublish the live shop,
            // or just flag for manual review. Flag-only shown here:
            // Shop::where('id', $scraped->shop_id)->update(['needs_review' => true]);
            $flagged++;
        }

        // Touch verification timestamp on everything still open
        ScrapedShop::where('status', 'approved')
            ->whereNotIn('place_id', $validated['closed_place_ids'])
            ->update(['last_verified_at' => now()]);

        return response()->json(['flagged' => $flagged]);
    }
}

// ===========================================================================
// 4. ROUTES — add to routes/api.php
// ===========================================================================

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::post('/shops/import', [ShopImportController::class, 'store']);
    Route::get('/shops/verify', [ShopImportController::class, 'verifyList']);
    Route::post('/shops/verify', [ShopImportController::class, 'verifyReport']);
});

// ===========================================================================
// 5a. COMMAND — app/Console/Commands/ShopsReview.php
// ===========================================================================

namespace App\Console\Commands;

use App\Models\ScrapedShop;
use Illuminate\Console\Command;

class ShopsReview extends Command
{
    protected $signature = 'shops:review
                            {--all : Show all scraped shops, not just pending}
                            {--state= : Filter by two-letter state}
                            {--limit=25}';

    protected $description = 'List scraped card shops awaiting review';

    public function handle(): int
    {
        $query = ScrapedShop::query()->orderBy('state')->orderBy('city');

        if (! $this->option('all')) {
            $query->pending();
        }
        if ($state = $this->option('state')) {
            $query->where('state', strtoupper($state));
        }

        $shops = $query->limit((int) $this->option('limit'))->get();

        if ($shops->isEmpty()) {
            $this->info('Nothing pending. Queue is clear.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'City', 'ST', 'Rating', 'Phone', 'Status'],
            $shops->map(fn ($s) => [
                $s->id,
                mb_strimwidth($s->name, 0, 35, '…'),
                $s->city ?? '—',
                $s->state ?? '—',
                $s->rating ? "{$s->rating} ({$s->rating_count})" : '—',
                $s->phone ?? '—',
                $s->status,
            ])
        );

        $this->line('Pending total: ' . ScrapedShop::pending()->count());
        $this->line('Approve with: php artisan shops:approve <id> <id> ...');

        return self::SUCCESS;
    }
}

// ===========================================================================
// 5b. COMMAND — app/Console/Commands/ShopsApprove.php
// ===========================================================================

namespace App\Console\Commands;

use App\Models\ScrapedShop;
use App\Models\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShopsApprove extends Command
{
    protected $signature = 'shops:approve {ids* : ScrapedShop IDs to promote}';

    protected $description = 'Promote scraped shops to the live shops directory';

    public function handle(): int
    {
        $approved = 0;

        foreach ($this->argument('ids') as $id) {
            $scraped = ScrapedShop::find($id);

            if (! $scraped) {
                $this->error("ID {$id}: not found, skipping.");

                continue;
            }
            if ($scraped->status !== 'pending') {
                $this->warn("ID {$id}: already {$scraped->status}, skipping.");

                continue;
            }

            DB::transaction(function () use ($scraped) {
                $shop = $this->promote($scraped);
                $scraped->update(['status' => 'approved', 'shop_id' => $shop->id]);
            });

            $this->info("ID {$id}: approved → \"{$scraped->name}\" is live.");
            $approved++;
        }

        $this->line("Done. {$approved} shop(s) promoted.");

        return self::SUCCESS;
    }

    /**
     * TODO: Map to your real Shop model columns — check your shops
     * migration for exact names and adjust.
     */
    private function promote(ScrapedShop $scraped): Shop
    {
        return Shop::create([
            'name' => $scraped->name,
            'address' => $scraped->address,
            'city' => $scraped->city,
            'state' => $scraped->state,
            'zip_code' => $scraped->zip_code,
            'lat' => $scraped->lat,
            'lng' => $scraped->lng,
            'phone' => $scraped->phone,
            'website' => $scraped->website,
            'hours' => $scraped->hours,
        ]);
    }
}

// ===========================================================================
// 5c. COMMAND — app/Console/Commands/ShopsReject.php
// ===========================================================================

namespace App\Console\Commands;

use App\Models\ScrapedShop;
use Illuminate\Console\Command;

class ShopsReject extends Command
{
    protected $signature = 'shops:reject {ids* : ScrapedShop IDs to reject}';

    protected $description = 'Reject scraped shops (kept to block re-import)';

    public function handle(): int
    {
        foreach ($this->argument('ids') as $id) {
            $scraped = ScrapedShop::find($id);

            if (! $scraped) {
                $this->error("ID {$id}: not found, skipping.");

                continue;
            }
            if ($scraped->status !== 'pending') {
                $this->warn("ID {$id}: already {$scraped->status}, skipping.");

                continue;
            }

            $scraped->update(['status' => 'rejected']);
            $this->info("ID {$id}: rejected → \"{$scraped->name}\"");
        }

        return self::SUCCESS;
    }
}

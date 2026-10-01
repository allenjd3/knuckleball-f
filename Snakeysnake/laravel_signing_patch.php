<?php

/*
|--------------------------------------------------------------------------
| Patch: extend the events import pipeline for player signings
|--------------------------------------------------------------------------
| The signing scraper reuses POST /api/events/import. Three small changes
| to the pieces you already have:
|
|   1. New migration (adds columns to scraped_events)
|   2. Validator additions in EventImportController
|   3. Fillable + promote() updates
*/

// ===========================================================================
// 1. MIGRATION — database/migrations/xxxx_add_signing_fields_to_scraped_events.php
// ===========================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scraped_events', function (Blueprint $table) {
            $table->string('event_type')->default('card_show')->index()->after('contact');
            $table->string('event_format')->default('in_person')->after('event_type');
            $table->string('player_name')->nullable()->after('event_format');
        });
    }

    public function down(): void
    {
        Schema::table('scraped_events', function (Blueprint $table) {
            $table->dropColumn(['event_type', 'event_format', 'player_name']);
        });
    }
};

// ===========================================================================
// 2. VALIDATOR — add these rules inside EventImportController::store()
// ===========================================================================

/*
    'events.*.event_type'   => ['nullable', 'in:card_show,player_signing,comic_con,memorabilia_show'],
    'events.*.event_format' => ['nullable', 'in:in_person,mail_in'],
    'events.*.player_name'  => ['nullable', 'string', 'max:255'],
*/

// ===========================================================================
// 3. MODEL + COMMAND UPDATES
// ===========================================================================

/*
ScrapedEvent model — add to $fillable:
    'event_type', 'event_format', 'player_name',

EventsApprove::promote() — replace the hardcoded type/format lines:

    'type'   => $scraped->event_type,     // was 'card_show'
    'format' => $scraped->event_format,   // was 'in_person'
    // TODO: still match these to your real Event column names

If signings link to player pages on your site, this is the bonus play —
match player_name against your players table during approval:

    $player = Player::where('name', $scraped->player_name)->first();
    'player_id' => $player?->id,

A signing that's linked to its player page means the event shows up on
Barry Larkin's profile, not just the events list. That cross-linking is
what your seeded Barry Larkin Day listing already does manually.

EventsReview — add Type column to the table() call:
    $e->event_type,
And optionally a filter:
    {--type= : Filter by event type}
    if ($type = $this->option('type')) { $query->where('event_type', $type); }
*/

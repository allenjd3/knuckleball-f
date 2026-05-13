<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite cannot alter column constraints — recreate via raw SQL
        DB::statement('CREATE TABLE featured_listings_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            event_id INTEGER NULL REFERENCES events(id) ON DELETE SET NULL,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            plan_type VARCHAR NOT NULL,
            stripe_payment_intent_id VARCHAR NULL,
            stripe_subscription_id VARCHAR NULL,
            amount_paid NUMERIC NOT NULL,
            starts_at DATETIME NOT NULL,
            expires_at DATETIME NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )');
        DB::statement('INSERT INTO featured_listings_new SELECT * FROM featured_listings');
        DB::statement('DROP TABLE featured_listings');
        DB::statement('ALTER TABLE featured_listings_new RENAME TO featured_listings');
    }

    public function down(): void
    {
        // Not easily reversible in SQLite
    }
};

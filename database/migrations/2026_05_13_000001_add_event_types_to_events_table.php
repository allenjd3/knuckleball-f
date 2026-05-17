<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE events MODIFY COLUMN type ENUM('player_signing', 'card_show', 'comic_con', 'memorabilia_show') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE events MODIFY COLUMN type ENUM('player_signing', 'card_show') NOT NULL");
    }
};

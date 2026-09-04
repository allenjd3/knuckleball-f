<?php

use App\Models\Player;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
        });

        // Backfill from the existing `name` column so the sort works for
        // players that already exist; new/updated players get this set by
        // Player::booted() from here on.
        Player::query()->select(['id', 'name'])->chunkById(500, function ($players) {
            foreach ($players as $player) {
                Player::withoutTimestamps(
                    fn () => Player::whereKey($player->id)->update(['last_name' => Player::lastNameFrom($player->name)])
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('last_name');
        });
    }
};

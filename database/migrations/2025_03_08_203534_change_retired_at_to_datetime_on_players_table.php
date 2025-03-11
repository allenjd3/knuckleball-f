<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->datetime('retired_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('datetime_on_players', function (Blueprint $table) {
            //
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('nullable_on_players', function (Blueprint $table) {
            //
        });
    }
};

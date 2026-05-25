<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('type', ['player_signing', 'card_show', 'comic_con', 'memorabilia_show'])->change();
        });
    }

    public function down(): void {}
};

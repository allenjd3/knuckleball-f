<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_shops', function (Blueprint $table) {
            $table->string('place_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('card_shops', function (Blueprint $table) {
            $table->dropUnique(['place_id']);
            $table->dropColumn('place_id');
        });
    }
};

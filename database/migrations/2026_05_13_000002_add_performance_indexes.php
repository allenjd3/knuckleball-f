<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index('status');
            $table->index('start_date');
            $table->index('is_featured');
            $table->index(['latitude', 'longitude']);
        });

        Schema::table('card_shops', function (Blueprint $table) {
            $table->index('status');
            $table->index('is_featured');
            $table->index(['latitude', 'longitude']);
        });

        Schema::table('set_entries', function (Blueprint $table) {
            $table->index(['set_id', 'status']);
        });

        Schema::table('feeds', function (Blueprint $table) {
            $table->index('followable_id');
        });
    }

    public function down(): void {}
};

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
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['start_date']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['latitude', 'longitude']);
        });

        Schema::table('card_shops', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['latitude', 'longitude']);
        });

        Schema::table('set_entries', function (Blueprint $table) {
            $table->dropIndex(['set_id', 'status']);
        });
    }
};

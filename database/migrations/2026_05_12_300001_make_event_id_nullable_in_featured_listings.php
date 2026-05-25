<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('featured_listings', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->foreignId('event_id')->nullable()->change();
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('featured_listings', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->foreignId('event_id')->nullable(false)->change();
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }
};

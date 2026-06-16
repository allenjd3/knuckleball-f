<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('description');
            $table->string('source_hash', 16)->nullable()->unique()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['source_hash']);
            $table->dropColumn(['notes', 'source_hash']);
        });
    }
};

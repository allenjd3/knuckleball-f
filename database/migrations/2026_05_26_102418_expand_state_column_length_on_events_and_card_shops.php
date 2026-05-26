<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('state', 3)->nullable()->change();
        });

        Schema::table('card_shops', function (Blueprint $table): void {
            $table->string('state', 3)->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('state', 2)->nullable()->change();
        });

        Schema::table('card_shops', function (Blueprint $table): void {
            $table->string('state', 2)->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('country', 2)->nullable()->default('US')->after('state');
        });

        Schema::table('card_shops', function (Blueprint $table) {
            $table->string('country', 2)->nullable()->default('US')->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('events', fn (Blueprint $t) => $t->dropColumn('country'));
        Schema::table('card_shops', fn (Blueprint $t) => $t->dropColumn('country'));
    }
};

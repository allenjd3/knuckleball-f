<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('zip_code', 10)->nullable()->after('bio');
            $table->unsignedSmallInteger('radius')->default(50)->after('zip_code');
            $table->boolean('card_show_alerts')->default(false)->after('radius');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['zip_code', 'radius', 'card_show_alerts']);
        });
    }
};

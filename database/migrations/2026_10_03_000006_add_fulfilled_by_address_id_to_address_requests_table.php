<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('address_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('fulfilled_by_address_id')->nullable()->after('fulfilled_at');
            $table->index('fulfilled_by_address_id');
        });
    }

    public function down(): void
    {
        Schema::table('address_requests', function (Blueprint $table) {
            $table->dropIndex(['fulfilled_by_address_id']);
            $table->dropColumn('fulfilled_by_address_id');
        });
    }
};

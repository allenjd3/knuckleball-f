<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('in_person_autographs', function (Blueprint $table) {
            $table->boolean('is_declined')->default(false)->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('in_person_autographs', function (Blueprint $table) {
            $table->dropColumn('is_declined');
        });
    }
};

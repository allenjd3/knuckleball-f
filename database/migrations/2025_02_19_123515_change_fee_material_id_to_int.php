<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postal_mails', function (Blueprint $table) {
            $table->unsignedBigInteger('fee_material_id')->change();
        });
    }
};

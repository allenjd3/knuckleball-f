<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fee_material_postal_mail', function (Blueprint $table) {
            $table->unsignedBigInteger('postal_mail_id');
            $table->unsignedBigInteger('fee_material_id');
        });
    }
};

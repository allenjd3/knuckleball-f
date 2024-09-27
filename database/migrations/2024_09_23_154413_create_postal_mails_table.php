<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postal_mails', function (Blueprint $table) {
            $table->id();
            $table->string('fee_material_id');
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('date_sent');
            $table->timestamp('returned_date')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }
};

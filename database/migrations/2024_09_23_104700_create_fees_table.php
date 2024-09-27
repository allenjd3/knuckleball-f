<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('fee_material_id');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('followables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id');
            $table->foreignId('followable_id');
            $table->timestamps();
        });
    }
};

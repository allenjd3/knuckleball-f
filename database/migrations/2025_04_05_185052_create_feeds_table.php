<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('followable_id');
            $table->morphs('feedable');
            $table->string('comment', 500);
            $table->json('meta');
            $table->timestamps();
        });
    }
};

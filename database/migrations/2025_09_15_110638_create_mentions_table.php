<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id');
            $table->foreignId('user_id');
            $table->foreignId('mentioned_by_id');
            $table->string('feed_type');
            $table->timestamps();

            $table->index(['feed_id', 'user_id']);
        });
    }
};

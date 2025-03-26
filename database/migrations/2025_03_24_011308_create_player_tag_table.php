<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('tag_id');
            $table->datetime('approved_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_tag');
    }
};

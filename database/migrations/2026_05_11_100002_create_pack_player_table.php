<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pack_player', function (Blueprint $table) {
            $table->foreignId('pack_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->string('status')->default('want_to_send');
            $table->timestamps();
            $table->primary(['pack_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_player');
    }
};

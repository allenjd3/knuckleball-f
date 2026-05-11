<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('want_list_followers', function (Blueprint $table) {
            $table->foreignId('want_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['want_list_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('want_list_followers');
    }
};

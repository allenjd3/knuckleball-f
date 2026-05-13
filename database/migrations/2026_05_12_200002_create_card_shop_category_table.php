<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_shop_category', function (Blueprint $table) {
            $table->foreignId('card_shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['card_shop_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_shop_category');
    }
};

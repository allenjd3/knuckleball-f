<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('postal_mail_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('manufacturer');
            $table->string('series');
            $table->integer('year');
            $table->string('number')->nullable();
            $table->string('variation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invite_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->integer('remaining')->default(0);
            $table->boolean('is_unlimited')->default(false);
            $table->timestamps();
        });
    }
};

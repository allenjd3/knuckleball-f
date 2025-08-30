<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_graphs', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048);
            $table->string('path', 2048);
            $table->string('disk', 50);
            $table->string('title');
            $table->string('description');
            $table->timestamps();
        });
    }
};

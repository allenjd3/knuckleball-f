<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signer_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('signer_id');
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('user_id');
            $table->datetime('approved_at')->nullable();

            $table->unique(['signer_id', 'tag_id']);
            $table->index(['signer_id', 'user_id']);
        });
    }
};

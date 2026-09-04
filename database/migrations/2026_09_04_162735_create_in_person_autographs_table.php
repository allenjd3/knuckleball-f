<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('in_person_autographs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('signer_id');
            $table->date('obtained_date');
            $table->unsignedBigInteger('fee_material_id')->nullable();
            $table->string('location')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('signer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('in_person_autographs');
    }
};

<?php

use App\Models\Address;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dateTime('published_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
        });

        Address::query()->update(['published_at' => now()->subDay()]);
    }
};

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
            $table->unsignedBigInteger('signer_id')->nullable();
        });

        Address::lazy()->each(function ($address) {
            $address->update(['signer_id' => $address->player->signer->id]);
        });
    }
};

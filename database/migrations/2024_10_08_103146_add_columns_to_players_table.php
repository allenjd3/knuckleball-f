<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up (): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedBigInteger('last_team_id')->after('slug')->nullable();
            $table->timestamp('retired_at')->after('slug')->nullable();
        });
    }
};

<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('handle')->nullable();
            $table->index(['handle']);
        });

        User::whereNull('handle')
            ->lazyById()
            ->each(fn ($user) => $user->update([
                'handle' => str($user->slug)->camel(),
            ]));
    }
};

<?php

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('body', 300);
            $table->foreignIdFor(User::class)->nullable();
            $table->foreignIdFor(Comment::class)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};

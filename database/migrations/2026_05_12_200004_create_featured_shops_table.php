<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('featured_shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan_type'); // monthly, yearly
            $table->string('stripe_subscription_id')->nullable();
            $table->string('status')->default('active'); // active, cancelled, past_due
            $table->decimal('amount_paid', 8, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_shops');
    }
};

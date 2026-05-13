<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['player_signing', 'card_show']);
            $table->string('name');

            // Player signing specific
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('event_subtype', ['in_person', 'mail_in'])->nullable();

            // Date & time
            $table->boolean('is_multi_day')->default(false);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Location
            $table->string('venue_name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Fees & options (player signings)
            $table->decimal('fees_per_item', 8, 2)->nullable();
            $table->unsignedSmallInteger('max_items')->nullable();
            $table->boolean('personalization_allowed')->default(false);
            $table->boolean('photo_op_available')->default(false);
            $table->decimal('photo_op_price', 8, 2)->nullable();
            $table->boolean('vip_available')->default(false);
            $table->decimal('vip_price', 8, 2)->nullable();
            $table->text('vip_perks')->nullable();

            // Details
            $table->text('special_instructions')->nullable();
            $table->boolean('registration_required')->default(false);
            $table->string('registration_link')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->unsignedInteger('estimated_attendance')->nullable();
            $table->text('parking_info')->nullable();
            $table->text('accessibility_notes')->nullable();
            $table->string('age_restrictions')->nullable();

            // Card show specific
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->json('photos')->nullable();
            $table->string('promoter_name')->nullable();

            // Mail-in specific
            $table->date('submission_deadline')->nullable();
            $table->date('expected_return_by')->nullable();
            $table->string('make_check_payable_to')->nullable();
            $table->boolean('return_envelope_required')->default(false);
            $table->json('payment_methods')->nullable();

            // Ownership & status
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

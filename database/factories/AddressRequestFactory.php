<?php

namespace Database\Factories;

use App\Enums\AddressRequestReason;
use App\Models\AddressRequest;
use App\Models\Signer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AddressRequest>
 */
class AddressRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'signer_id' => Signer::factory(),
            'reason' => AddressRequestReason::MissingAddress,
            'note' => null,
        ];
    }

    public function returnToSender(): static
    {
        return $this->state(['reason' => AddressRequestReason::ReturnToSender]);
    }

    public function fulfilled(): static
    {
        return $this->state(['fulfilled_at' => now()]);
    }
}

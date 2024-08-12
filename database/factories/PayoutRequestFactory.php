<?php

namespace Database\Factories;

use App\Enums\RequestStatus;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PayoutRequest>
 */
class PayoutRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'country_id' => Country::inRandomOrder()->first()->id,
            'full_name' => fake()->name,
            'bank_name' => fake()->company,
            'bank_account' => fake()->creditCardNumber,
            'status' => $status = fake()->randomElement(RequestStatus::values()),
            'amount' => $status === RequestStatus::Complete ? fake()->numberBetween(100, 1000) : null,
        ];
    }
}

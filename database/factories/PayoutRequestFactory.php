<?php

namespace Database\Factories;

use App\Enums\RequestStatus;
use App\Enums\UserType;
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
            'user_id' => User::where('type',UserType::Artist)->inRandomOrder()->first()->id,
            'country_id' => Country::inRandomOrder()->first()->id,
            'full_name' => fake()->name,
            'id_no' => mt_rand(1000000000, 9999999999),
            'phone' => fake()->phoneNumber,
            'status' => $status = fake()->randomElement(RequestStatus::values()),
            'amount' => $status === RequestStatus::Complete ? fake()->numberBetween(100, 1000) : null,
        ];
    }
}

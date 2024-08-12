<?php

namespace Database\Factories;

use App\Enums\RequestStatus;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InfluencerRequest>
 */
class InfluencerRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => Country::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'snapchat' => fake()->userName,
            'instagram' => fake()->userName,
            'tiktok' => fake()->userName,
            'status' => fake()->randomElement(RequestStatus::values()),
        ];
    }
}

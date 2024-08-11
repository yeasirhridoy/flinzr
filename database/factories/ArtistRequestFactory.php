<?php

namespace Database\Factories;

use App\Enums\PlatformType;
use App\Enums\RequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ArtistRequest>
 */
class ArtistRequestFactory extends Factory
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
            'url' => fake()->url,
            'status' => fake()->randomElement(RequestStatus::values()),
            'platform' => fake()->randomElement(PlatformType::values()),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\PlatformType;
use App\Enums\RequestStatus;
use App\Enums\UserType;
use App\Models\Country;
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
            'country_id' => Country::inRandomOrder()->first()->id,
            'user_id' => User::where('type',UserType::Artist)->inRandomOrder()->first()->id,
            'full_name' => fake()->name,
            'phone' => fake()->phoneNumber,
            'id_no' => mt_rand(1000000000, 9999999999),
            'url' => fake()->url,
            'status' => fake()->randomElement(RequestStatus::values()),
        ];
    }
}

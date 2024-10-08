<?php

namespace Database\Factories;

use App\Enums\PlatformType;
use App\Enums\RequestStatus;
use App\Enums\UserType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpecialRequest>
 */
class SpecialRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('type',UserType::Customer)->inRandomOrder()->first()->id,
            'category_id' => Category::inRandomOrder()->first()->id,
            'platform' => fake()->randomElement(PlatformType::values()),
            'occasion' => fake()->word(),
            'image' => 'special-requests/01J5G4FDCH1C8R02F89BGZ1EMN.jpg',
            'status' => fake()->randomElement(RequestStatus::values()),
            'created_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }
}

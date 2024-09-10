<?php

namespace Database\Factories;

use App\Enums\UserType;
use App\Models\Filter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filter_id' => Filter::query()->inRandomOrder()->first()?->id,
            'user_id' => User::query()->inRandomOrder()->first()?->id,
            'artist_id' => User::query()->where('type', UserType::Artist)->inRandomOrder()->first()?->id,
            'earning' => $earning = $this->faker->numberBetween(1, 10),
            'amount' => $earning * 2,
            'created_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }
}

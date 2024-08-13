<?php

namespace Database\Factories;

use App\Enums\UserType;
use App\Models\Category;
use App\Models\Color;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()->id,
            'user_id' => User::where('type',UserType::Artist)->inRandomOrder()->first()->id,
            'type' => fake()->randomElement(['banner','snapchat','tiktok','instagram']),
            'eng_name' => fake()->word,
            'arabic_name' => fake()->word,
            'eng_description' => fake()->sentence,
            'arabic_description' => fake()->sentence,
            'sales_type'=> fake()->randomElement(['free','paid','subscription']),
            'is_active' => $isActive = fake()->boolean,
            'is_featured' => $isActive && fake()->boolean,
        ];
    }
}

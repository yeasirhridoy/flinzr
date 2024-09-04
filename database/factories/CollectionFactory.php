<?php

namespace Database\Factories;

use App\Enums\UserType;
use App\Models\Category;
use App\Models\Color;
use App\Models\Region;
use App\Models\Tag;
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
            'is_trending' => $isActive && fake()->boolean,
            'avatar' => '01J6Z5EZV437HXBAJKVN4Q8RR3.jpg',
            'thumbnail' => '01J6Z5EZV437HXBAJKVN4Q8RR3.jpg',
            'cover' => '01J6Z5EZV437HXBAJKVN4Q8RR3.jpg'
        ];
    }

    public function addTags(): Factory|CollectionFactory
    {
        return $this->afterCreating(function ($collection) {
            $collection->tags()->attach(Tag::inRandomOrder()->limit(3)->get());
        });
    }

    public function addColors(): Factory|CollectionFactory
    {
        return $this->afterCreating(function ($collection) {
            $collection->colors()->attach(Color::inRandomOrder()->limit(3)->get());
        });
    }

    public function addRegions(): Factory|CollectionFactory
    {
        return $this->afterCreating(function ($collection) {
            $collection->regions()->attach(Region::inRandomOrder()->limit(2)->get());
        });
    }
}

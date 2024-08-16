<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommissionLevel>
 */
class CommissionLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level_1_target' =>500,
            'level_1_commission' => 10,
            'level_2_target' =>1000,
            'level_2_commission' =>15,
            'level_3_target' => 2000,
            'level_3_commission' => 20,
            'level_4_target' => 4000,
            'level_4_commission' => 25,
            'level_5_target' => 8000,
            'level_5_commission' => 30,
            'level_6_target' => 16000,
            'level_6_commission' => 35,
            'level_7_target' =>32000,
            'level_7_commission' => 40,
            'level_8_target' => 64000,
            'level_8_commission' => 45,
        ];
    }
}

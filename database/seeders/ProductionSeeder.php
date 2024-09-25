<?php

namespace Database\Seeders;

use App\Models\CommissionLevel;
use App\Models\ExternalLink;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExternalLink::factory()->create();
        CommissionLevel::factory()->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\ArtistRequest;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Color;
use App\Models\CommissionLevel;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\ExternalLink;
use App\Models\InfluencerRequest;
use App\Models\PayoutRequest;
use App\Models\Region;
use App\Models\SpecialRequest;
use App\Models\Tag;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
        ]);
        Region::factory(5)->sequence(
            ['name' => 'Region 1'],
            ['name' => 'Region 2'],
            ['name' => 'Region 3'],
            ['name' => 'Region 4'],
            ['name' => 'Region 5'],
        )->create();
        $countries = Country::all();
        $regions = Region::all();
        foreach ($countries as $country) {
            $country->regions()->attach($regions->random());
        }
        Color::factory(5)->sequence(
            ['eng_name' => 'Red', 'arabic_name' => 'Red', 'code' => '#FF0000'],
            ['eng_name' => 'Blue', 'arabic_name' => 'Blue', 'code' => '#0000FF'],
            ['eng_name' => 'Green', 'arabic_name' => 'Green', 'code' => '#00FF00'],
            ['eng_name' => 'Yellow', 'arabic_name' => 'Yellow', 'code' => '#FFFF00'],
            ['eng_name' => 'Black', 'arabic_name' => 'Black', 'code' => '#000000'],
        )->create();
        Tag::factory(5)->sequence(
            ['eng_name' => 'Tag 1'],
            ['eng_name' => 'Tag 2'],
            ['eng_name' => 'Tag 3'],
            ['eng_name' => 'Tag 4'],
            ['eng_name' => 'Tag 5'],
        )->create();
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);
        User::factory(50)->create();
        Category::factory(5)->has(
            Collection::factory(5)->sequence(
                ['eng_name' => 'Anniversary', 'arabic_name' => 'Anniversary'],
                ['eng_name' => 'Birthday', 'arabic_name' => 'Birthday'],
                ['eng_name' => 'Eid', 'arabic_name' => 'Eid'],
                ['eng_name' => 'Congratulations', 'arabic_name' => 'Congratulations'],
                ['eng_name' => 'Easter', 'arabic_name' => 'Easter'],
            )
        )->create();

        ArtistRequest::factory(10)->create();
        InfluencerRequest::factory(10)->create();
        PayoutRequest::factory(10)->create();
        SpecialRequest::factory(10)->has(
            Conversation::factory(mt_rand(20,100))
        )->create();
        ExternalLink::factory()->create();
        CommissionLevel::factory()->create();
    }
}

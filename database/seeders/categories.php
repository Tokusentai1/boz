<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\sub_category;
use App\Models\product;
use App\Models\address;
use App\Models\category;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class categories extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Seed categories
        for ($i = 0; $i < 5; $i++) {
            $category = category::create([
                'picture' => $faker->imageUrl(),
                'name' => $faker->word,
            ]);

            // Seed sub-categories for each category
            for ($j = 0; $j < 3; $j++) {
                $subCategory = sub_category::create([
                    'picture' => $faker->imageUrl(),
                    'name' => $faker->word,
                    'category_id' => $category->id,
                ]);

                // Seed products for each sub-category
                for ($k = 0; $k < 5; $k++) {
                    product::create([
                        'name' => $faker->word,
                        'description' => $faker->sentence,
                        'picture' => $faker->imageUrl(),
                        'quantity' => $faker->numberBetween(1, 100),
                        'price' => $faker->randomFloat(2, 1, 100),
                        'calories' => $faker->numberBetween(50, 1000),
                        'sub_category_id' => $subCategory->id,
                    ]);
                }
            }
        }
    }
}

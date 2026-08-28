<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'SCUM Danny Trejo Character Pack', 'category' => 'PC Digital'],
            ['name' => 'Super Loco World Cozy Train Automation', 'category' => 'PC Digital'],
            ['name' => 'WUCHANG: Fallen Feathers', 'category' => 'PC Digital'],
            ['name' => 'Cyberpunk 2077', 'category' => 'PC Digital'],
            ['name' => 'Red Dead Redemption 2', 'category' => 'PC Digital'],
        ];

        foreach ($products as $product) {
            Product::query()->create($product);
        }
    }
}

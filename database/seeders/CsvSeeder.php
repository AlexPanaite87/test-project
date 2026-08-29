<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class CsvSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = fopen(database_path('seeders/csv/products.csv'), 'r');

        fgetcsv($csvFile);

        while (($data = fgetcsv($csvFile)) !== false) {
            Product::query()->create([
                'name' => $data[1],
                'category' => $data[2],
            ]);
        }

        fclose($csvFile);
    }
}

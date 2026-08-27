<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = fopen(database_path("seeders/csv/products.csv"), "r");
        $firstLine = true;

        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (!$firstLine) {
                Product::create([
                    'name' => $data[1],
                    'category' => $data[2],
                    'youtube_url' => empty($data[3]) ? null : $data[3],
                ]);
            }
            $firstLine = false;
        }
        fclose($csvFile);
    }
}

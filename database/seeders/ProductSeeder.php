<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use App\Models\Brand;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Generate 20 random products
        for ($i = 0; $i < 10; $i++) {
            Product::create([
                'category_id' => 1,
                'brand_id' => 1,
                'name' => 'temporary name',
                'slug' => 'temporary-slug'. $i,
                'sku' => 'TEMporarySKU'. $i,
                'description' => "description",
                'price' => rand(100, 5000),
                'stock' => rand(0, 100),
                'product_images' => "images",
            ]);
        }
    }
}

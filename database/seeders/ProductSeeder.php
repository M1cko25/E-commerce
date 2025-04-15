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
        $products = [
            'category_id' => Category::inRandomOrder()->first()->id,
            'brand_id' => Brand::inRandomOrder()->first()->id,
            'name' => 'temporary name',
            'slug' => 'temporary-slug'. rand(1, 1000),
            'sku' => 'TEMporarySKU'. rand(1, 1000),
            'description' => "description",
            'price' => rand(100, 5000),
            'stock' => rand(0, 100),
            'product_images' => "images",
        ];
        for ($i = 0; $i < 10; $i++) {
            Product::create($products);
        }
    }
}

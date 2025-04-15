<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run()
    {
        Brand::create([
            'name' => 'No Brand',
            'slug' => 'no-brand',
            'description' => 'No Brand is a generic brand for products without a specific brand name.',
            'logo' => 'images/no_brand_logo.png'
        ]);
    }
}

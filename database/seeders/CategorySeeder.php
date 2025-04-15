<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        
        Category::create([
            'name' => 'No Category',
            'slug' => 'no-category',
            'description' => 'No Category is a generic category for products without a specific category name.',
            'image' => 'images/no_category_image.png'
        ]);
    }
}

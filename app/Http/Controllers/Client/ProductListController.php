<?php

namespace App\Http\Controllers\Client;

use Inertia\Inertia;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductListController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->select(['id', 'name', 'slug', 'description', 'brand_id', 'category_id', 'product_images', 'price'])
            ->with([
                'category:id,name',
                'brand:id,name',
                'variants' => function($query) {
                    $query->select('product_variants.id', 'product_variants.product_id', 'product_variants.price')
                          ->orderBy('price', 'asc')
                          ->limit(1);
                },
                'specifications' => function($query) {
                    $query->select('specifications.id', 'specifications.name', 'product_specifications.value', 'product_specifications.product_id');
                }
            ])
            ->filter($request->only([
                'search',
                'priceRange',
                'inStock',
                'brands',
                'categories'
            ]));

        $filteredProductIds = (clone $query)->pluck('id');

        $categories = Category::select(['categories.id', 'categories.name'])
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->whereIn('products.id', $filteredProductIds)
            ->groupBy('categories.id', 'categories.name')
            ->select([
                'categories.id',
                'categories.name',
                DB::raw('COUNT(products.id) as products_count')
            ])
            ->havingRaw('COUNT(products.id) > 0')
            ->get();

        $brands = Brand::select(['brands.id', 'brands.name'])
            ->leftJoin('products', 'brands.id', '=', 'products.brand_id')
            ->whereIn('products.id', $filteredProductIds)
            ->groupBy('brands.id', 'brands.name')
            ->select([
                'brands.id',
                'brands.name',
                DB::raw('COUNT(products.id) as products_count')
            ])
            ->havingRaw('COUNT(products.id) > 0')
            ->get();

        $products = $query->paginate(16);

        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->variants->first()->price ?? $product->price,
                'product_images' => $product->product_images,
                'category' => $product->category->name,
                'brand' => $product->brand->name,
                'variants' => $product->variants,
                'specifications' => $product->specifications->map(function($spec) {
                    return [
                        'name' => $spec->name,
                        'value' => $spec->value
                    ];
                })
            ];
        });

        return Inertia::render('ClientSide/ProductList', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'searchTerm' => $request->search,
            'filters' => $request->only(['priceRange', 'inStock', 'brands', 'categories'])
        ]);
    }
}

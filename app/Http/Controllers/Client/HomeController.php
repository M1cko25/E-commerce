<?php

namespace App\Http\Controllers\Client;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LandingContent;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::all();
        $brands = Brand::all();
        $landingContents = LandingContent::where('is_active', 1)->get();
        $exploreProducts = Product::with(['category', 'brand', 'specifications'])
            ->take(0)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'brand' => $product->brand->name,
                    'category' => $product->category->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => $product->product_images->first() ?? null,
                    'specifications' => $product->specifications
                ];
            })
            ->random(0);
        $latestProducts = Product::with(['category', 'brand', 'specifications'])
            ->latest()
            ->take(0)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'brand' => $product->brand->name,
                    'category' => $product->category->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => $product->product_images->first() ?? null,
                    'specifications' => $product->specifications
                        ->map(fn($spec) => [
                            'name' => $spec->name,
                            'value' => $spec->pivot->value
                        ])
                ];
            });

        return Inertia::render('ClientSide/GuestHome', [
            'exploreProducts' => $exploreProducts,
            'latestProducts' => $latestProducts,
            'categories' => $categories,
            'brands' => $brands,
            'landingContents' => $landingContents
        ]);
        } catch (\Exception $e) {
            Log::error('Error in HomeController@index: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'An error occurred while loading the home page.');
        }
    }
}

<?php

namespace App\Http\Controllers\Client;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LandingContent;
use Illuminate\Support\Facades\Auth;
use App\Models\WishlistItem;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $landingContents = LandingContent::where('is_active', 1)->get();
        $wishlistProductIds = [];
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
            $wishlistProductIds = WishlistItem::where('customer_id', $customer->id)
                ->pluck('product_id')
                ->toArray();
        }
        $exploreProducts = Product::with(['category', 'brand', 'specifications'])
            ->take(3)
            ->get()
            ->map(function ($product) use ($wishlistProductIds) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'brand' => $product->brand->name,
                    'category' => $product->category->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'in_wishlist' => in_array($product->id, $wishlistProductIds),
                    'image' => $product->product_images[0] ?? null,
                    'specifications' => $product->specifications
                ];
            })
            ->random(3);
        $latestProducts = Product::with(['category', 'brand', 'specifications'])
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($product) use ($wishlistProductIds) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'brand' => $product->brand->name,
                    'category' => $product->category->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'in_wishlist' => in_array($product->id, $wishlistProductIds),
                    'image' => $product->product_images[0] ?? null,
                    'specifications' => $product->specifications
                        ->map(fn($spec) => [
                            'name' => $spec->name,
                            'value' => $spec->pivot->value
                        ])
                ];
            });

        $chatId = 0;
        if (session()->has('chat_id')) {
            $chatId = session('chat_id');
        }

        return Inertia::render('ClientSide/GuestHome', [
            'exploreProducts' => $exploreProducts,
            'latestProducts' => $latestProducts,
            'categories' => $categories,
            'brands' => $brands,
            'landingContents' => $landingContents,
            'chatId' => $chatId
        ]);
    }
}

<?php

namespace App\Http\Controllers\Client;

use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ProductViewController extends Controller
{
    public function index($slug) {
        $product = Product::where('slug', $slug)
            ->with([
                'brand',
                'category',
                'variants',
                'specifications' => function($query) {
                    $query->select('specifications.id', 'specifications.name', 'product_specifications.value');
                }
            ])
            ->firstOrFail();

        // Check if product is in wishlist
        $inWishlist = false;
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
            $inWishlist = WishlistItem::where('customer_id', $customer->id)
                ->where('product_id', $product->id)
                ->exists();
        }

        $similarProducts = Product::where(function($query) use ($product) {
                $query->where('category_id', $product->category_id)
                      ->orWhere('brand_id', $product->brand_id);
            })
            ->where('id', '!=', $product->id)
            ->with('brand')
            ->take(10)
            ->get();

        // Get wishlist items for similar products
        $wishlistProductIds = [];
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
            $productIds = $similarProducts->pluck('id')->toArray();
            $wishlistProductIds = WishlistItem::where('customer_id', $customer->id)
                ->whereIn('product_id', $productIds)
                ->pluck('product_id')
                ->toArray();
        }

        $mappedSimilarProducts = $similarProducts->map(function ($product) use ($wishlistProductIds) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'rating' => $product->rating ?? 5,
                'reviewCount' => $product->review_count ?? 0,
                'image' => $product->product_images[0] ?? null,
                'brand' => $product->brand->name,
                'in_wishlist' => in_array($product->id, $wishlistProductIds),
                'specifications' => $product->specifications->map(function($spec) {
                    return [
                        'name' => $spec->name,
                        'value' => $spec->value
                    ];
                })
            ];
        });

        // Include the inWishlist property
        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'category' => $product->category,
            'brand' => $product->brand,
            'price' => $product->price,
            'stock' => $product->stock,
            'product_images' => $product->product_images,
            'variants' => $product->variants,
            'specifications' => $product->specifications,
            'in_wishlist' => $inWishlist,
        ];

        return Inertia::render('ClientSide/ProductView', [
            'product' => $productData,
            'similarProducts' => $mappedSimilarProducts
        ]);
    }
}

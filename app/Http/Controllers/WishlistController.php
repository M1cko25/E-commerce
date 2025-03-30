<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WishlistController extends Controller
{
    /**
     * Display the customer's wishlist
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        // Get wishlist items with product details
        $wishlistItems = WishlistItem::where('customer_id', $customer->id)
            ->with(['product.brand', 'product.category'])
            ->latest('added_at')
            ->get();

        $formattedItems = $wishlistItems->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'added_at' => $item->added_at->format('Y-m-d'),
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'price' => $item->product->price,
                    'stock' => $item->product->stock,
                    'image' => $item->product->product_images[0] ?? null,
                    'brand' => $item->product->brand->name ?? 'Unknown',
                    'category' => $item->product->category->name ?? 'Unknown',
                ]
            ];
        });

        return Inertia::render('ClientSide/Customer/Wishlist', [
            'customer' => $customer,
            'wishlistItems' => $formattedItems
        ]);
    }

    /**
     * Add a product to the wishlist
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $customer = Auth::guard('customer')->user();

        // Check if product is already in wishlist
        $exists = WishlistItem::where('customer_id', $customer->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Product is already in your wishlist'
            ], 200);
        }

        // Add to wishlist
        WishlistItem::create([
            'customer_id' => $customer->id,
            'product_id' => $request->product_id,
            'added_at' => now()
        ]);

        return response()->json([
            'message' => 'Product added to wishlist'
        ], 201);
    }

    /**
     * Remove an item from the wishlist
     */
    public function destroy($id)
    {
        $customer = Auth::guard('customer')->user();

        $wishlistItem = WishlistItem::where('id', $id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $wishlistItem->delete();

        return redirect()->back()->with('success', 'Item removed from wishlist');
    }

    /**
     * Toggle a product in the wishlist (add if not exists, remove if exists)
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $customer = Auth::guard('customer')->user();

        // Check if product is already in wishlist
        $wishlistItem = WishlistItem::where('customer_id', $customer->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlistItem) {
            // Remove from wishlist
            $wishlistItem->delete();
            return redirect()->back()->with('success', 'Product removed from wishlist');
        } else {
            // Add to wishlist
            WishlistItem::create([
                'customer_id' => $customer->id,
                'product_id' => $request->product_id,
                'added_at' => now()
            ]);

            return redirect()->back()->with('success', 'Product added to wishlist');
        }
    }
}

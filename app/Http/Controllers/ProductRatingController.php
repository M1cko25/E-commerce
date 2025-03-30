<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductRatingController extends Controller
{
    /**
     * Store a new product rating
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        // Check if customer is authenticated
        if (!Auth::guard('customer')->check()) {
            return response()->json([
                'message' => 'You must be logged in to rate products'
            ], 401);
        }

        $customer = Auth::guard('customer')->user();

        // Check if customer has already rated this product
        $existingRating = ProductRating::where('customer_id', $customer->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingRating) {
            // Update existing rating
            $existingRating->update([
                'rating' => $request->rating,
                'review' => $request->review ?? $existingRating->review,
            ]);

            // Recalculate product rating
            $this->updateProductRating($request->product_id);

            return redirect()->back()->with('success', 'Rating updated successfully');
        }

        // Create new rating
        $rating = ProductRating::create([
            'customer_id' => $customer->id,
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        // Recalculate product rating
        $this->updateProductRating($request->product_id);

        return redirect()->back()->with('success', 'Rating submitted successfully');
    }

    /**
     * Update the average rating for a product
     */
    private function updateProductRating($productId)
    {
        $product = Product::findOrFail($productId);

        $ratings = ProductRating::where('product_id', $productId)->get();
        $averageRating = $ratings->avg('rating');
        $ratingCount = $ratings->count();

        $product->update([
            'rating' => round($averageRating, 1),
            'rating_count' => $ratingCount
        ]);
    }

    /**
     * Get ratings for a product
     */
    public function getProductRatings($productId)
    {
        $ratings = ProductRating::where('product_id', $productId)
            ->with('customer:id,first_name,last_name')
            ->latest()
            ->paginate(10);

        return response()->json($ratings);
    }
}

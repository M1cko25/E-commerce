<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ProductSpecification;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductVariants;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $products = Product::with(['category', 'brand'])->paginate(10);
        return Inertia::render('AdminSide/Products/Index', [
            'products'=> $products
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('AdminSide/Products/Create', [
            'categories' => Category::with('specifications')->get(),
            'brands' => Brand::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'sizes' => 'nullable|array',
            'sizes.*' => 'string',
            'kinds' => 'nullable|array',
            'kinds.*' => 'string',
            'images.*' => 'nullable|file|image|max:10240',
            'specifications' => 'nullable|array',
            'specifications.*.id' => 'required|integer|exists:specifications,id',
            'specifications.*.value' => 'required|string|max:255',
            'variants' => 'array',
            'variants.*.size' => 'required|string',
            'variants.*.kind' => 'nullable|string',
            'variants.*.price' => 'required|numeric|min:0',
        ]);

        try {
            // Handle image uploads
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $images[] = $image->store('product_images', 'public');
                }
            }

            // Set the price from the first variant if variants exist
            $price = 0;
            if (!empty($validated['variants'])) {
                $price = $validated['variants'][0]['price'];
            } else {
                $price = $validated['price'];
            }

            // Save product
            $product = Product::create(array_merge(
                collect($validated)->except(['sizes', 'kinds', 'variants', 'specifications'])->toArray(),
                ['product_images' => $images, 'sku' => '', 'price' => $price]
            ));

            // Generate SKU
            $category = Category::find($validated['category_id']);
            $product->update(['sku' => $category->sku . '-' . $product->id]);

            // Create product variants
            if (!empty($validated['variants'])) {
                foreach ($validated['variants'] as $variant) {
                    ProductVariants::create([
                        'product_id' => $product->id,
                        'sizes' => $variant['size'],
                        'kinds' => $variant['kind'],
                        'price' => $variant['price']
                    ]);
                }
            } elseif (!empty($validated['sizes']) || !empty($validated['kinds'])) {
                foreach ($validated['sizes'] as $size) {
                    foreach ($validated['kinds'] ?? [''] as $kind) {
                        ProductVariants::create([
                            'product_id' => $product->id,
                            'sizes' => $size,
                            'kinds' => $kind ?: null,
                            'price' => $price
                        ]);
                    }
                }
            }

            // Attach specifications
            if (!empty($validated['specifications'])) {
                foreach ($validated['specifications'] as $spec) {
                    ProductSpecification::create([
                        'product_id' => $product->id,
                        'spec_id' => $spec['id'],
                        'value' => $spec['value'],
                    ]);
                }
            }

            return redirect()->route('products.index')->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return Inertia::render('AdminSide/Products/Edit', [
            'product' => $product->load([
                'specifications' => function($query) {
                    $query->select('specifications.id', 'specifications.name', 'product_specifications.value');
                },
                'variants'
            ]),
            'categories' => Category::with('specifications')->get(),
            'brands' => Brand::all(),
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $product->id,
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'description' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required_without:variants|numeric|min:0',
            'product_images' => 'nullable',
            'remove_images' => 'nullable',
            'variants' => 'nullable',
            'specifications' => 'nullable',
        ]);

        try {
            // Start DB transaction
            DB::beginTransaction();

            // Parse JSON for arrays submitted as strings
            $remove_images = $request->remove_images;
            if (is_string($remove_images)) {
                $remove_images = json_decode($remove_images, true);
            }

            $variants = $request->variants;
            if (is_string($variants)) {
                $variants = json_decode($variants, true);
            }

            $specifications = $request->specifications;
            if (is_string($specifications)) {
                $specifications = json_decode($specifications, true);
            }

            // We'll handle product_images parsing after image removal

            // Handle image uploads
            $newImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $newImages[] = $image->store('product_images', 'public');
                }
            }

            // Handle image removals if there are images to remove
            $currentImages = $product->product_images ?? [];
            if (!empty($remove_images)) {
                Log::info('Removing images:', $remove_images);

                foreach ($remove_images as $image) {
                    // Search for the image path in the current images array
                    if (in_array($image, $currentImages)) {
                        Log::info('Found image to remove: ' . $image);

                        // Remove from storage
                        if (Storage::exists('public/' . $image)) {
                            Storage::delete('public/' . $image);
                            Log::info('Deleted image from storage: ' . $image);
                        }

                        // Remove from the currentImages array
                        $currentImages = array_values(array_filter($currentImages, function($img) use ($image) {
                            return $img !== $image;
                        }));
                    }
                }
            }

            // Get the remaining images from the form
            $product_images = $request->product_images;
            if (is_string($product_images)) {
                $product_images = json_decode($product_images, true) ?? [];
            }

            // If we have explicit product_images from the form, use those
            // Otherwise fall back to the filtered currentImages
            $remainingImages = !empty($product_images) ? $product_images : $currentImages;

            // Merge remaining and new images
            $allImages = array_merge(
                is_array($remainingImages) ? $remainingImages : [],
                $newImages
            );

            // Set the price from the first variant if variants exist
            $price = $validated['price'] ?? 0;
            if (!empty($variants)) {
                $price = $variants[0]['price'];
            }

            // Update product with basic information
            $product->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'sku' => $validated['sku'],
                'description' => $validated['description'],
                'product_images' => array_values($allImages),
                'stock' => $validated['stock'],
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'price' => $price
            ]);

            // Update variants
            $product->variants()->delete(); // Remove existing variants
            if (!empty($variants)) {
                foreach ($variants as $variant) {
                    $product->variants()->create([
                        'sizes' => $variant['size'],
                        'kinds' => $variant['kind'] ?? null,
                        'price' => $variant['price']
                    ]);
                }
            }

            // Update specifications
            if (!empty($specifications)) {
                $product->specifications()->detach();
                foreach ($specifications as $spec) {
                    ProductSpecification::create([
                        'product_id' => $product->id,
                        'spec_id' => $spec['id'],
                        'value' => $spec['value']
                    ]);
                }
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();

            // Log the error for debugging
            Log::error('Product update error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            // Delete product images from storage
            if (!empty($product->product_images) && is_array($product->product_images)) {
                foreach ($product->product_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            // Delete product specifications
            $product->specifications()->detach();

            // Delete the product
            $product->delete();

            return redirect()->route('products.index')
                ->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error deleting product: ' . $e->getMessage()]);
        }
    }
}


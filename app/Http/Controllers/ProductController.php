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
use DB;

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
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required_without:variants|numeric|min:0',
            'variants' => 'array',
            'variants.*.size' => 'required|string',
            'variants.*.kind' => 'nullable|string',
            'variants.*.price' => 'required|numeric|min:0',
            'specifications' => 'array',
            'specifications.*.name' => 'required|string',
            'specifications.*.value' => 'required|string',
            'remove_images' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Handle image uploads
            $newImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->storeAs('public/products', $imageName);
                    $newImages[] = 'products/' . $imageName;
                }
            }

            // Handle image removals
            $currentImages = $product->product_images ?? [];
            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $image) {
                    if (($key = array_search($image, $currentImages)) !== false) {
                        Storage::delete('public/' . $image);
                        unset($currentImages[$key]);
                    }
                }
            }

            // Merge existing and new images
            $allImages = array_merge($currentImages, $newImages);

            // Set the price from the first variant if variants exist
            $price = $validated['price'] ?? 0;
            if (!empty($validated['variants'])) {
                $price = $validated['variants'][0]['price'];
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
            if (!empty($validated['variants'])) {
                foreach ($validated['variants'] as $variant) {
                    $product->variants()->create([
                        'sizes' => $variant['size'],
                        'kinds' => $variant['kind'],
                        'price' => $variant['price']
                    ]);
                }
            }

            // Update specifications
            $product->specifications()->delete();
            if (!empty($validated['specifications'])) {
                foreach ($validated['specifications'] as $spec) {
                    ProductSpecification::create([
                        'product_id' => $product->id,
                        'name' => $spec['name'],
                        'value' => $spec['value']
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
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
            if (!empty($product->product_images)) {
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

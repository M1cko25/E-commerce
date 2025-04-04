<?php

namespace App\Http\Controllers\Client;

use Inertia\Inertia;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Product;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = [];

        if (Auth::guard('customer')->check()) {
            // Get cart items from database for authenticated users
            $cartItems = CartItem::where('customer_id', Auth::guard('customer')->id())
                ->with('product')
                ->get()
                ->map(function ($item) {
                    return [
                        $item->product_id,      // [0] id
                        $item->product->name,    // [1] name
                        $item->price,           // [2] price
                        $item->quantity,        // [3] quantity
                        $item->product->product_images[0] ?? null, // [4] image
                        $item->selected ?? true, // [5] selected - default to true if null
                        $item->product->slug,    // [6] slug for product link
                        $item->product->sizes,   // [7] available sizes array
                        $item->product->kinds,   // [8] available kinds array
                        $item->size ?? '',       // [9] selected size
                        $item->kind ?? '',       // [10] selected kind
                    ];
                })
                ->keyBy(function ($item) {
                    return $item[0];
                })
                ->toArray();
        } else {
            // Get cart items from session for guests
            $cart = Session::get('cart', []);
            foreach ($cart as $productId => $item) {
                // Fetch the product to get its slug and other details
                $product = Product::find($productId);
                if ($product) {
                    $cartItems[$productId] = [
                        $productId,           // [0] id
                        $item['name'],        // [1] name
                        $item['price'],       // [2] price
                        $item['quantity'],    // [3] quantity
                        $item['image'],       // [4] image
                        $item['selected'],    // [5] selected
                        $product->slug,       // [6] slug for product link
                        $product->sizes,      // [7] available sizes array
                        $product->kinds,      // [8] available kinds array
                        $item['size'] ?? '',  // [9] selected size
                        $item['kind'] ?? '',  // [10] selected kind
                    ];
                }
            }
        }

        return Inertia::render('ClientSide/Cart', [
            'cart' => $cartItems
        ]);
    }

    public function addToCart(Request $request)
    {
        if (Auth::guard('customer')->check()) {
            // For authenticated users, save to database
            $cartItem = CartItem::where('customer_id', Auth::guard('customer')->id())
                ->where('product_id', $request->product_id)
                ->first();

            if ($cartItem) {
                // If item exists, add the new quantity to the existing quantity
                $cartItem->update([
                    'quantity' => $cartItem->quantity + $request->quantity
                ]);
            } else {
                // If item doesn't exist, create new
                CartItem::create([
                    'customer_id' => Auth::guard('customer')->id(),
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                    'price' => $request->price,
                    'selected' => true
                ]);
            }
        } else {
            // For guests, save to session
            $cart = Session::get('cart', []);
            $productId = $request->product_id;

            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $request->quantity;
            } else {
                $cart[$productId] = [
                    'name' => $request->name,
                    'price' => $request->price,
                    'quantity' => $request->quantity,
                    'image' => $request->image,
                    'selected' => true,
                    'size' => $request->size ?? '',
                    'kind' => $request->kind ?? ''
                ];
            }

            Session::put('cart', $cart);
        }

        return back()->with('success', 'Product added to cart successfully');
    }

    public function updateQuantity(Request $request)
    {
        if (Auth::guard('customer')->check()) {
            // For authenticated users, update database
            CartItem::where('customer_id', Auth::guard('customer')->id())
                ->where('product_id', $request->product_id)
                ->update(['quantity' => $request->quantity]);
        } else {
            // For guests, update session
            $cart = Session::get('cart', []);
            $productId = $request->product_id;

            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $request->quantity;
                Session::put('cart', $cart);
            }
        }

        return back()->with('success', 'Quantity updated successfully');
    }

    public function removeFromCart(Request $request)
    {
        if (Auth::guard('customer')->check()) {
            // For authenticated users, delete from database
            CartItem::where('customer_id', Auth::guard('customer')->id())
                ->where('product_id', $request->product_id)
                ->delete();
        } else {
            // For guests, remove from session
            $cart = Session::get('cart', []);
            $productId = $request->product_id;

            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                Session::put('cart', $cart);
            }
        }

        return back()->with('success', 'Product removed from cart successfully');
    }

    public function updateSelection(Request $request)
    {
        if (Auth::guard('customer')->check()) {
            // For authenticated users, update database
            CartItem::where('customer_id', Auth::guard('customer')->id())
                ->where('product_id', $request->product_id)
                ->update(['selected' => $request->selected]);
        } else {
            // For guests, update session
            $cart = Session::get('cart', []);
            $productId = $request->product_id;

            if (isset($cart[$productId])) {
                $cart[$productId]['selected'] = $request->selected;
                Session::put('cart', $cart);
            }
        }

        return back()->with('success', 'Selection updated successfully');
    }

    public function updateAllSelections(Request $request)
    {
        if (Auth::guard('customer')->check()) {
            // For authenticated users, update all items in database
            CartItem::where('customer_id', Auth::guard('customer')->id())
                ->update(['selected' => $request->selected]);
        } else {
            // For guests, update all items in session
            $cart = Session::get('cart', []);

            foreach ($cart as $productId => $item) {
                $cart[$productId]['selected'] = $request->selected;
            }

            Session::put('cart', $cart);
        }

        return back()->with('success', 'All selections updated successfully');
    }

    public function clearCart()
    {
        if (Auth::guard('customer')->check()) {
            // For authenticated users, clear database items
            CartItem::where('customer_id', Auth::guard('customer')->id())->delete();
        } else {
            // For guests, clear session
            Session::forget('cart');
        }

        return back()->with('success', 'Cart cleared successfully');
    }

    public function addMultiple(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.image' => 'required|string',
            'items.*.size' => 'nullable|string',
            'items.*.kind' => 'nullable|string'
        ]);

        if (Auth::guard('customer')->check()) {
            // For authenticated users, bulk insert to database
            foreach ($request->items as $item) {
                CartItem::updateOrCreate(
                    [
                        'customer_id' => Auth::guard('customer')->id(),
                        'product_id' => $item['product_id']
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'size' => $item['size'] ?? '',
                        'kind' => $item['kind'] ?? ''
                    ]
                );
            }
        } else {
            // For guests, add multiple items to session
            $cart = Session::get('cart', []);

            foreach ($request->items as $item) {
                $productId = $item['product_id'];
                $cart[$productId] = [
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'image' => $item['image'],
                    'selected' => true,
                    'size' => $item['size'] ?? '',
                    'kind' => $item['kind'] ?? ''
                ];
            }

            Session::put('cart', $cart);
        }

        return back()->with('success', 'All items added to cart successfully');
    }

    public function updateOptions(Request $request)
    {
        if (Auth::guard('customer')->check()) {
            // For authenticated users, update database
            CartItem::where('customer_id', Auth::guard('customer')->id())
                ->where('product_id', $request->product_id)
                ->update([
                    'size' => $request->size,
                    'kind' => $request->kind
                ]);
        } else {
            // For guests, update session
            $cart = Session::get('cart', []);
            $productId = $request->product_id;

            if (isset($cart[$productId])) {
                $cart[$productId]['size'] = $request->size;
                $cart[$productId]['kind'] = $request->kind;
                Session::put('cart', $cart);
            }
        }

        return back()->with('success', 'Options updated successfully');
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'buy_now' => 'nullable|boolean'
        ]);

        $customer = Auth::guard('customer')->user();
        $product = Product::findOrFail($request->product_id);

        // Check product stock
        if ($product->stock < $request->quantity) {
            return back()->withErrors(['error' => 'Not enough stock available.']);
        }

        // Check if product is already in cart
        $cartItem = CartItem::where('customer_id', $customer->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            // Update quantity
            $cartItem->update([
                'quantity' => $cartItem->quantity + $request->quantity,
                'selected' => true // Select this item by default
            ]);
        } else {
            // Add new item
            $cartItem = CartItem::create([
                'customer_id' => $customer->id,
                'product_id' => $request->product_id,
                'price' => $product->price,
                'quantity' => $request->quantity,
                'selected' => true // Select this item by default
            ]);
        }

        // For buy now, deselect all other cart items
        if ($request->buy_now) {
            CartItem::where('customer_id', $customer->id)
                ->where('id', '!=', $cartItem->id)
                ->update(['selected' => false]);

            return response()->json([
                'message' => 'Product added to cart',
                'redirect' => route('customer.checkout')
            ]);
        }

        return back()->with('success', 'Product added to cart.');
    }
}

<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Orders;
use App\Models\CartItem;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use App\Models\CustomerAddresses;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Http\Controllers\QRController;

class CheckoutController extends Controller
{
    public function index()
    {
        // Check if user has any selected items in cart
        $hasItems = CartItem::where('customer_id', Auth::guard('customer')->id())
            ->where('selected', true)
            ->exists();

        if (!$hasItems) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Please add items before proceeding to checkout.');
        }

        // Get authenticated customer
        $customer = Auth::guard('customer')->user();
        $defaultAddress = $customer->default_address_id
            ? CustomerAddresses::find($customer->default_address_id)
            : null;

        // Get all customer addresses
        $addresses = CustomerAddresses::where('customer_id', $customer->id)->get();

        // Get selected cart items
        $selectedItems = CartItem::where('customer_id', $customer->id)
            ->where('selected', true)
            ->with('product')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'image' => $item->product->product_images[0] ?? null,
                    'subtotal' => $item->price * $item->quantity
                ];
            });

        // Calculate totals
        $subtotal = $selectedItems->sum('subtotal');
        $shipping = request('delivery_method') === 'pickup' ? 0 : 145;
        $total = $subtotal + $shipping;

        return Inertia::render('ClientSide/Customer/Checkout', [
            'customer' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $defaultAddress,
            ],
            'addresses' => $addresses,
            'items' => $selectedItems,
            'summary' => [
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total
            ],
        ]);
    }

    public function pay(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        // Get address either from address_id or default
        $address = null;
        if ($request->address_id) {
            $address = CustomerAddresses::where('customer_id', $customer->id)
                ->where('id', $request->address_id)
                ->first();
        } else if ($customer->default_address_id) {
            $address = CustomerAddresses::find($customer->default_address_id);
        }

        if (!$address) {
            return back()->withErrors(['error' => 'Please select a delivery address first.']);
        }

        // Get selected cart items
        $selectedItems = CartItem::where('customer_id', $customer->id)
            ->where('selected', true)
            ->with('product')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'image' => $item->product->product_images[0] ?? null,
                    'subtotal' => $item->price * $item->quantity
                ];
            });

        if ($selectedItems->isEmpty()) {
            return back()->withErrors(['error' => 'No items selected for checkout.']);
        }

        // Calculate totals
        $subtotal = $selectedItems->sum('subtotal');
        $shipping = request('delivery_method') === 'pickup' ? 0 : 145;
        $total = $subtotal + $shipping;

        // Store payment data in session
        Session::put('payment_data', [
            'selected_items' => $selectedItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'payment_method' => 'gcash',
            'customer' => $customer,
            'notes' => $request->input('notes'),  // Get notes from request
            'shipping_address' => $request->input('shipping_address'),  // Get shipping address from request
            'address_id' => $request->address_id,
        ]);

        // If using QR payment method, redirect to the QR payment page
        if ($request->input('payment_option') === 'qr_code') {
            return redirect()->route('customer.qrPayment');
        }

        // Continue with original GCash payment flow (Paymongo)
        try {
            // Create Paymongo source
            $data = [
                'data' => [
                    'attributes' => [
                        'amount' => $total * 100, // Convert to cents
                        'currency' => 'PHP',
                        'type' => 'gcash',
                        'redirect' => [
                            'success' => route('customer.payment.success'),
                            'failed' => route('customer.checkout'),
                        ],
                        'billing' => [
                            'name' => $customer->first_name . ' ' . $customer->last_name,
                            'email' => $customer->email,
                            'phone' => $customer->phone,
                            'address' => [
                                'line1' => $address->complete_address,
                                'city' => $address->city,
                                'state' => $address->state,
                                'postal_code' => $address->zip_code,
                                'country' => 'PH'
                            ]
                        ]
                    ]
                ]
            ];

            $response = Curl::to('https://api.paymongo.com/v1/sources')
                ->withHeader('Content-Type: application/json')
                ->withHeader('Accept: application/json')
                ->withHeader('Authorization: Basic ' . base64_encode(env('AUTH_PAY') . ':'))
                ->withData($data)
                ->asJson()
                ->post();

            if (!$response || isset($response->errors)) {
                throw new \Exception('Failed to create payment source');
            }

            // Update session with the response ID
            $paymentData = Session::get('payment_data');
            $paymentData['response_id'] = $response->data->id;
            Session::put('payment_data', $paymentData);

            // Return the checkout URL in an Inertia response
            return Inertia::location($response->data->attributes->redirect->checkout_url);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Payment initialization failed. Please try again.']);
        }
    }

    public function success()
    {
        $paymentData = Session::get('payment_data');

        if (!$paymentData) {
            return redirect()->route('customer.checkout')->withErrors(['error' => 'Payment session expired.']);
        }

        // Create the order with shipping address and notes
        $order = Orders::create([
            'reference_number' => 'ORD-' . uniqid(),
            'customer_id' => $paymentData['customer']->id,
            'total_amount' => $paymentData['total'],
            'payment_method' => $paymentData['payment_method'],
            'shipping_method' => 'delivery',
            'shipping_amount' => $paymentData['shipping'],
            'status' => 'pending',
            'payment_status' => 'paid',
            'notes' => $paymentData['notes'] ?? null,
            'shipping_address' => $paymentData['shipping_address'],
            'shipping_address_id' => $paymentData['address_id'] ?? null,
        ]);

        // Create order items
        foreach ($paymentData['selected_items'] as $item) {
            OrderItems::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_amount' => $item['price'],
                'total_amount' => $item['subtotal']
            ]);
        }

        // Clear selected cart items
        CartItem::where('customer_id', $paymentData['customer']->id)
            ->where('selected', true)
            ->delete();

        Session::forget('payment_data');

        return redirect()->route('customer.myOrders')->with('success', 'Payment successful! Your order has been placed.');
    }

    /**
     * Process Cash on Delivery order
     */
    public function processCod(Request $request)
    {
        // Validate the request
        $request->validate([
            'notes' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'address_id' => 'nullable|exists:customer_addresses,id',
            'delivery_method' => 'required|string|in:delivery,pickup',
            'payment_method' => 'required|string|in:cash,gcash',
        ]);

        // Get customer information
        $customer = Auth::guard('customer')->user();

        // Get address either from address_id or default
        $address = null;
        if ($request->address_id) {
            $address = CustomerAddresses::where('customer_id', $customer->id)
                ->where('id', $request->address_id)
                ->first();
        } else if ($customer->default_address_id) {
            $address = CustomerAddresses::find($customer->default_address_id);
        }

        if (!$address && $request->input('delivery_method') === 'delivery') {
            return back()->withErrors(['error' => 'Please select a delivery address first.']);
        }

        // Get selected cart items
        $selectedItems = CartItem::where('customer_id', $customer->id)
            ->where('selected', true)
            ->with('product')
            ->get();

        if ($selectedItems->isEmpty()) {
            return back()->withErrors(['error' => 'No items selected for checkout.']);
        }

        // Calculate totals
        $subtotal = $selectedItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $shipping = $request->input('delivery_method') === 'pickup' ? 0 : 145;
        $total = $subtotal + $shipping;

        try {
            DB::beginTransaction();

            // Create the order
            $order = Orders::create([
                'reference_number' => 'ORD-' . uniqid(),
                'customer_id' => $customer->id,
                'total_amount' => $total,
                'payment_method' => $request->input('payment_method', 'cod'),
                'shipping_method' => $request->input('delivery_method', 'delivery'),
                'shipping_amount' => $shipping,
                'payment_status' => 'pending',
                'notes' => $request->input('notes'),
                'shipping_address' => $address ? "{$address->complete_address}, {$address->city}, {$address->province}, {$address->zip_code}" : null,
                'order_status' => 'pending'
            ]);

            // Create order items and update stock
            foreach ($selectedItems as $item) {
                // Create order item
                OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_amount' => $item->price,
                    'total_amount' => $item->price * $item->quantity
                ]);

                // Update product stock
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->stock = max(0, $product->stock - $item->quantity);
                    $product->save();
                }
            }

            // Clear selected cart items
            CartItem::where('customer_id', $customer->id)
                ->where('selected', true)
                ->delete();

            DB::commit();

            // Redirect to home with success message
            return redirect()->route('customer.myOrders')->with('success', 'Your order has been placed successfully! You can track your order status here.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process your order. Please try again. ' . $e->getMessage()]);
        }
    }

    /**
     * Process GCash payment and display the QR code.
     */
    public function showQrPayment(Request $request)
    {
        // Get authenticated customer
        $customer = Auth::guard('customer')->user();

        // Get order information from session or create new order
        if (!Session::has('payment_data')) {
            return redirect()->route('customer.checkout')->withErrors(['error' => 'Payment session expired. Please try again.']);
        }

        $paymentData = Session::get('payment_data');

        // Get a random QR code from the database
        $qrCode = app(QRController::class)->getRandomQrCode();

        // Create temporary order or get from session
        if (!isset($paymentData['order_id'])) {
            // Create temporary order in pending state
            $order = Orders::create([
                'reference_number' => 'ORD-' . uniqid(),
                'customer_id' => $customer->id,
                'total_amount' => $paymentData['total'],
                'payment_method' => 'gcash',
                'shipping_method' => 'delivery',
                'shipping_amount' => $paymentData['shipping'],
                'payment_status' => 'pending',
                'notes' => $paymentData['notes'] ?? null,
                'shipping_address' => $paymentData['shipping_address'],
                'shipping_address_id' => $paymentData['address_id'] ?? null,
            ]);

            // Create order items
            foreach ($paymentData['selected_items'] as $item) {
                OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_amount' => $item['price'],
                    'total_amount' => $item['subtotal']
                ]);
            }

            // Store order ID in session
            $paymentData['order_id'] = $order->id;
            Session::put('payment_data', $paymentData);
        } else {
            // Get existing order
            $order = Orders::findOrFail($paymentData['order_id']);
        }

        return Inertia::render('ClientSide/Customer/Pay', [
            'order' => $order,
            'qrCode' => $qrCode,
        ]);
    }

    /**
     * Confirm payment with reference number.
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_ref' => 'required|string|size:4|regex:/^\d+$/',
        ]);

        $order = Orders::findOrFail($request->order_id);

        // Update order with payment reference and mark as paid
        $order->payment_reference_number = $request->payment_ref;
        $order->payment_status = 'paid';
        $order->save();

        // Clear selected cart items
        CartItem::where('customer_id', Auth::guard('customer')->id())
            ->where('selected', true)
            ->delete();

        // Clear payment session
        Session::forget('payment_data');

        return redirect()->route('customer.myOrders')->with('success', 'Payment successful! Your order has been placed.');
    }
}

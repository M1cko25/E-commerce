<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Orders;
use App\Models\OrderItems; // Corrected import
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MyOrdersController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return redirect()->route('customer.login');
        }

        $orders = Orders::where('customer_id', $customer->id)
            ->with(['items.product']) // Proper eager loading
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'reference_number' => $order->reference_number,
                    'order_status' => $order->order_status,
                    'return_refund_status' => $order->return_refund_status,
                    'order_date' => $order->created_at->format('Y-m-d'),
                    'delivered_at' => $this->formatDateOrNull($order->delivered_at),
                    'total_amount' => $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_name' => $item->product->name ?? 'Unknown Product',
                            'category_id'  => $item->product->category_id ?? 'Unknown Category',
                            'brand_id' => $item->product->brand_id ?? 'Unknown Brand',
                            'product_image' => $item->product->product_images[0] ?? null,
                            'sku' => $item->product->sku ?? 'N/A',
                            'quantity' => $item->quantity,
                            'unit_amount' => $item->unit_amount,
                        ];
                    }),
                ];
            });

        return Inertia::render('ClientSide/Customer/MyOrders', [
            'orders' => $orders,
            'customer' => $customer,
        ]);
    }

    private function formatDateOrNull($date)
    {
        if (!$date) {
            return null;
        }

        try {
            if (is_string($date)) {
                // If it's a string, try to create a Carbon instance
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            }

            // If it's already a Carbon instance, just format it
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            // If any error occurs during formatting, return null
            return null;
        }
    }
}

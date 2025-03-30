<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerReturnController extends Controller
{
    /**
     * Show the return request form for an order
     */
    public function showReturnForm($orderReference)
    {
        $customer = Auth::guard('customer')->user();

        $order = Orders::where('reference_number', $orderReference)
            ->where('customer_id', $customer->id)
            ->where('order_status', 'delivered')
            ->where('return_refund_status', 'none')
            ->with(['items.product'])
            ->first();

        if (!$order) {
            return redirect()->route('customer.myOrders')
                ->with('error', 'Order not found or not eligible for return.');
        }

        return Inertia::render('ClientSide/Customer/ReturnRequest', [
            'order' => [
                'id' => $order->id,
                'reference_number' => $order->reference_number,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at,
                'order_status' => $order->order_status,
                'payment_method' => $order->payment_method,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->product->name ?? 'Unknown Product',
                        'quantity' => $item->quantity,
                        'unit_amount' => $item->unit_amount,
                        'total_amount' => $item->total_amount,
                        'image' => $item->product->image ?? null,
                    ];
                }),
            ],
            'customer' => $customer,
        ]);
    }

    /**
     * Process a return request
     */
    public function submitReturnRequest(Request $request, $orderReference)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'return_type' => 'required|in:return,refund',
        ]);

        $customer = Auth::guard('customer')->user();

        $order = Orders::where('reference_number', $orderReference)
            ->where('customer_id', $customer->id)
            ->where('order_status', 'delivered')
            ->where('return_refund_status', 'none')
            ->first();

        if (!$order) {
            return back()->with('error', 'Order not found or not eligible for return.');
        }

        // Update order with return request
        $order->notes = $request->reason;
        $order->return_refund_status = 'requested';
        $order->save();

        return redirect()->route('customer.myOrders')
            ->with('success', 'Your return request has been submitted and is pending review.');
    }

    /**
     * Cancel a return request
     */
    public function cancelReturnRequest($orderReference)
    {
        $customer = Auth::guard('customer')->user();

        $order = Orders::where('reference_number', $orderReference)
            ->where('customer_id', $customer->id)
            ->where('return_refund_status', 'requested')
            ->first();

        if (!$order) {
            return back()->with('error', 'Return request not found or cannot be cancelled.');
        }

        // Reset return status
        $order->return_refund_status = 'none';
        $order->notes = null;
        $order->save();

        return back()->with('success', 'Your return request has been cancelled.');
    }
}

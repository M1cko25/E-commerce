<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Product;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReturnController extends Controller
{
    /**
     * Display a listing of returns
     */
    public function index()
    {
        $returns = Orders::where('return_refund_status', '!=', 'none')
            ->with(['customer', 'items.product'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $formattedReturns = $returns->through(function ($order) {
            return [
                'id' => $order->id,
                'order_id' => $order->reference_number,
                'customer_name' => $order->customer->name,
                'customer_phone_number' => $order->customer->phone_number,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product->name ?? 'Unknown Product',
                        'quantity' => $item->quantity,
                        'unit_amount' => $item->unit_amount,
                        'total_amount' => $item->total_amount,
                    ];
                }),
                'reason' => $order->notes,
                'status' => $order->return_refund_status,
                'order_status' => $order->order_status,
                'created_at' => $order->created_at,
                'refund_amount' => $order->total_amount,
                'payment_method' => $order->payment_method,
            ];
        });

        return Inertia::render('AdminSide/Return/Index', [
            'returns' => $formattedReturns,
        ]);
    }

    /**
     * Process a return request approval
     */
    public function approveReturn(Request $request, $id)
    {
        $order = Orders::findOrFail($id);

        // Update order status
        $order->return_refund_status = 'approved';
        $order->order_status = 'returned';
        $order->save();

        // Return items to inventory
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->stock += $item->quantity;
                $product->save();
            }
        }

        return back()->with('success', 'Return request approved and inventory updated.');
    }

    /**
     * Process a refund request approval
     */
    public function approveRefund(Request $request, $id)
    {
        $order = Orders::findOrFail($id);

        // Update order status
        $order->return_refund_status = 'refunded';
        $order->save();

        // Note: Actual payment refund logic would be implemented here
        // This depends on your payment gateway integration

        return back()->with('success', 'Refund has been processed successfully.');
    }

    /**
     * Reject a return or refund request
     */
    public function rejectRequest(Request $request, $id)
    {
        $order = Orders::findOrFail($id);

        // Update order status back to delivered
        $order->return_refund_status = 'rejected';
        $order->save();

        return back()->with('success', 'Return/refund request has been rejected.');
    }

    /**
     * Delete a return request (admin only)
     */
    public function destroy($id)
    {
        $order = Orders::findOrFail($id);

        // Reset return status
        $order->return_refund_status = 'none';
        $order->save();

        return back()->with('success', 'Return request has been deleted.');
    }

    public function show($id)
    {
        $order = Orders::findOrFail($id);

        // Format the data to be used in the view
        $formattedOrder = [
            'id' => $order->id,
            'reference_number' => $order->reference_number,
            'order_id' => $order->reference_number, // Duplicate for consistent naming
            'customer_name' => $order->customer->first_name . ' ' . $order->customer->last_name,
            'customer_phone_number' => $order->customer->phone,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product->name ?? 'Unknown Product',
                    'quantity' => $item->quantity,
                    'unit_amount' => $item->unit_amount,
                    'total_amount' => $item->quantity * $item->unit_amount,
                    'product_id' => $item->product_id,
                ];
            }),
            'notes' => $order->notes,
            'return_refund_status' => $order->return_refund_status,
            'order_status' => $order->order_status,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'delivered_at' => $order->delivered_at,
            'total_amount' => $order->total_amount,
            'payment_method' => $order->payment_method,
        ];

        return Inertia::render('AdminSide/Return/Show', [
            'order' => $formattedOrder,
        ]);
    }
}

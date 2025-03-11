<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function placeOrder(Request $request)
    {
        \Log::info($request->all()); // Debugging

        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'cylinder_size' => 'required|string',
            'order_type' => 'required|string|in:new,refill',
        ]);

        // Extract numeric value from weight (if needed)
        $weight = preg_replace('/[^0-9.]/', '', $request->input('weight'));

        Order::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'cylinder_size' => $validated['cylinder_size'],
            'weight' => $weight, // Now it contains only numbers
            'order_type' => $validated['order_type'],
        ]);

        return response()->json(['message' => 'Order placed successfully!'], 201);
    }

    public function destroy(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids');

            if (empty($orderIds)) {
                return response()->json(['success' => false, 'message' => 'No orders selected.'], 400);
            }

            Order::whereIn('id', $orderIds)->delete();

            return response()->json(['success' => true, 'message' => 'Orders deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting orders.', 'error' => $e->getMessage()], 500);
        }
    }
}

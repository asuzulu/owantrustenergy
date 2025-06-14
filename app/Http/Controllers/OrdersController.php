<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Cylinder;
use App\Models\Warehouse;
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
            'retrieval'      => 'required|string|in:delivery,pick up',
        ]);

        // Extract numeric value from weight (if needed)
        $weight = preg_replace('/[^0-9.]/', '', $request->input('weight'));

        $user = Auth::user();

        $order = Order::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'customer_id' => $user->id,
            'cylinder_size' => $validated['cylinder_size'],
            'weight' => $weight,
            'order_type' => $validated['order_type'],
            'retrieval' => $validated['retrieval'],
        ]);

        \Log::info('Order created successfully', ['order_id' => $order->id]);

        return response()->json(['message' => 'Order placed successfully!'], 201);
    }

    public function destroy(Request $request)
    {
        \Log::info('Destroy method hit', ['order_ids' => $request->input('order_ids')]);

        try {
            $orderIds = $request->input('order_ids');

            if (empty($orderIds)) {
                \Log::warning('No orders selected for deletion.');
                return response()->json(['success' => false, 'message' => 'No orders selected.'], 400);
            }

            $deletedRows = Order::whereIn('id', $orderIds)->delete();

            \Log::info('Orders deleted', ['deleted_rows' => $deletedRows]);

            return response()->json(['success' => true, 'message' => 'Orders deleted successfully.']);
        } catch (\Exception $e) {
            \Log::error('Error deleting orders', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error deleting orders.', 'error' => $e->getMessage()], 500);
        }
    }

    //Customer order requests
    public function requests(Request $request)
    {
        $orders = Order::select('id', 'first_name', 'last_name', 'cylinder_size', 'weight', 'order_type', 'retrieval', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.requests', compact('orders'));
    }

    public function review(Order $order)
    {
        return view('orders.review', compact('order'));
    }

    /**
     * Return the matching customer (first+last name + position=Customer).
     */
    public function contactCustomer(Order $order)
    {
        $user = User::where('first_name', $order->first_name)
            ->where('last_name',  $order->last_name)
            ->where('position',   'Customer')
            ->firstOrFail([
                'first_name',
                'last_name',
                'phone_number',
                'email'
            ]);

        return response()->json($user);
    }

    /**
     * Find a random cylinder whose location is a warehouse name,
     * then redirect to its show page.
     */
    public function approveOrder(Order $order)
    {
        $warehouseNames = Warehouse::pluck('name')->toArray();

        $cylinder = Cylinder::whereIn('location', $warehouseNames)
            ->inRandomOrder()
            ->firstOrFail();

        $custName = urlencode($order->first_name . ' ' . $order->last_name);
        $assignType = $order->retrieval;

        return redirect()->to(
            route('cylinders.show', $cylinder->id) . "?openAssign=1&cust={$custName}&type={$assignType}"
        );
    }


    public function pickup()
    {
        return view('orders.pickup');
    }
}

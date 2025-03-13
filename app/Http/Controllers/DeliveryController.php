<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\Cylinder;
use App\Models\User;
use Carbon\Carbon;

class DeliveryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'cylinder_id' => 'required|exists:cylinders,id',
            'customer_id' => 'required|exists:users,id',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required',
        ]);

        $cylinder = Cylinder::findOrFail($request->cylinder_id);
        $customer = User::findOrFail($request->customer_id);
        $driver = User::findOrFail($request->driver_id);

        $address = trim("{$customer->street}, {$customer->city}, {$customer->state}", ", ");

        $delivery = Delivery::create([
            'driver_id' => $driver->id,
            'driver' => $driver->first_name . ' ' . $driver->last_name, // Store the full driver name
            'cylinder' => $cylinder->id,
            'size' => $cylinder->size,
            'address' => $address,
            'customer' => $customer->first_name . ' ' . $customer->last_name,
            'date_assigned' => Carbon::now()->toDateString(),
            'delivery_date' => $request->delivery_date,
            'delivery_time' => $request->delivery_time,
        ]);

        if ($delivery) {
            $cylinder->update([
                'user_id' => $customer->id,
                'location' => $customer->first_name . ' ' . $customer->last_name,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Cylinder assigned successfully']);
    }

    public function index()
    {
        $deliveries = Delivery::orderBy('date_assigned', 'desc')->paginate(15);
        return view('management.deliveries', compact('deliveries'));
    }

    public function destroy(Request $request)
    {
        $delivery = Delivery::find($request->id);
        if ($delivery) {
            $delivery->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }
}

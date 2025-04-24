<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\Cylinder;
use App\Models\User;
use Carbon\Carbon;

class DeliveryController extends Controller
{
    /**
     * Store a newly assigned delivery (via the Assign Cylinder modal).
     */
    public function store(Request $request)
    {
        $request->validate([
            'driver_id'     => 'required|exists:users,id',
            'cylinder_id'   => 'required|exists:cylinders,id',
            'customer_id'   => 'required|exists:users,id',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required',
        ]);

        $driver    = User::findOrFail($request->driver_id);
        $customer  = User::findOrFail($request->customer_id);
        $cylinderId = $request->input('cylinder_id'); // padded string, e.g. "000123456"
        $cylinder  = Cylinder::findOrFail($cylinderId);

        $address = trim("{$customer->street}, {$customer->city}, {$customer->state}", ", ");

        $delivery = Delivery::create([
            'driver_id'     => $driver->id,
            'driver'        => $driver->first_name . ' ' . $driver->last_name,
            'cylinder'      => $cylinderId,            // store the padded string
            'size'          => $cylinder->size,
            'address'       => $address,
            'customer'      => $customer->first_name . ' ' . $customer->last_name,
            'date_assigned' => Carbon::now()->toDateString(),
            'delivery_date' => $request->delivery_date,
            'delivery_time' => $request->delivery_time,
        ]);

        if ($delivery) {
            // Optionally update the cylinder’s user/location
            $cylinder->update([
                'user_id'  => $customer->id,
                'location' => $customer->first_name . ' ' . $customer->last_name,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cylinder assigned for delivery successfully.'
        ]);
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\Order;
use App\Models\Cylinder;
use App\Models\User;
use Carbon\Carbon;

class PickupController extends Controller
{
    public function index()
    {
        $pickups = Pickup::whereNull('date_picked_up')
            ->whereNull('time_picked_up')
            ->orderBy('date_assigned', 'desc')
            ->paginate(10);

        return view('orders.pickup', compact('pickups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cylinder_id' => 'required|exists:cylinders,id',
            'customer_id' => 'required|exists:users,id',
            'pickup_location' => 'required|string',
            'pick_up_date' => 'required|date',
        ]);

        $cylinder = Cylinder::findOrFail($request->cylinder_id);
        $customer = User::findOrFail($request->customer_id);

        $pickup = Pickup::create([
            'location' => $request->pickup_location,
            'cylinder' => $cylinder->id,
            'size' => $cylinder->size,
            'customer' => $customer->first_name . ' ' . $customer->last_name,
            'date_assigned' => Carbon::now()->toDateString(),
            'pick_up_date' => $request->pick_up_date,
        ]);

        if ($pickup) {
            $cylinder->update([
                'user_id' => $customer->id,
                'location' => $customer->first_name . ' ' . $customer->last_name,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Cylinder assigned for pick-up successfully']);
    }

    public function updatePickup(Request $request)
    {
        $request->validate([
            'pickup_id' => 'required|exists:pickups,id',
            'pickup_date' => 'required|date',
            'pickup_time' => 'required'
        ]);

        $pickup = Pickup::findOrFail($request->pickup_id);

        // Update the pickup details
        $pickup->update([
            'date_picked_up' => $request->pickup_date,
            'time_picked_up' => $request->pickup_time
        ]);

        return response()->json(['success' => true]);
    }
}

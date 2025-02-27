<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\Cylinder;
use App\Models\User;
use Carbon\Carbon;

class PickupController extends Controller
{
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
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
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
            'delivery_id'   => 'nullable|exists:deliveries,id',
        ]);

        $driver     = User::findOrFail($request->driver_id);
        $customer   = User::findOrFail($request->customer_id);
        $cylinderId = $request->input('cylinder_id');
        $cylinder   = Cylinder::findOrFail($cylinderId);

        $address = trim("{$customer->street}, {$customer->city}, {$customer->state}", ", ");

        // Either load existing stub or create a new instance
        if ($request->filled('delivery_id')) {
            $delivery = Delivery::findOrFail($request->delivery_id);
        } else {
            $delivery = new Delivery();
            $delivery->date_assigned = Carbon::now()->toDateString();

            // ── ALPHANUMERIC PASSCODE GENERATION (UPPERCASE + DIGITS ONLY) ────────────
            $chars    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $passcode = '';
            for ($i = 0; $i < 7; $i++) {
                $passcode .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $delivery->passcode = $passcode;
        }

        // Explicitly assign all fields, including customer_id
        $delivery->driver_id    = $driver->id;
        $delivery->driver       = $driver->first_name . ' ' . $driver->last_name;
        $delivery->cylinder     = $cylinderId; // padded string
        $delivery->size         = $cylinder->size;
        $delivery->address      = $address;
        $delivery->customer     = $customer->first_name . ' ' . $customer->last_name;
        $delivery->customer_id  = $customer->id;        // ✅ GUARANTEED
        $delivery->delivery_date = $request->delivery_date;
        $delivery->delivery_time = $request->delivery_time;
        $delivery->save();

        // Also update cylinder’s assignment
        $cylinder->update([
            'user_id'  => $customer->id,
            'location' => $customer->first_name . ' ' . $customer->last_name,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Delivery record saved successfully.',
            'delivery_id' => $delivery->id,
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

    public function deliveryListing(Request $request)
    {
        $query = Delivery::with('driverUser')->whereNotNull('image_path');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('cylinder', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%");
            });
        }

        $deliveries = $query->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->only('search'));

        return view('management.deliverypickup', compact('deliveries', 'search'));
    }

    public function updateApproval(Request $request)
    {
        $request->validate([
            'deliveries'   => 'required|array',
            'deliveries.*' => 'exists:deliveries,id',
            'action'       => 'required|in:approved,disapproved',
        ]);

        \DB::transaction(function () use ($request) {
            foreach ($request->deliveries as $id) {
                Delivery::where('id', $id)
                    ->update(['approval' => $request->action]);
            }
        });

        return redirect()->back()->with('success', 'Approval statuses updated.');
    }

    public function confirm(Request $request, $driverId)
    {
        // Only Manager, Employee or Agent can confirm
        $user = Auth::user();
        if (! in_array($user->position, ['Manager', 'Employee', 'Agent'])) {
            abort(403);
        }

        $data = $request->validate([
            'selected_deliveries' => 'required|array',
            'selected_deliveries.*' => 'exists:deliveries,id',
            'passcode' => 'required|string',
        ]);

        foreach ($data['selected_deliveries'] as $deliveryId) {
            $delivery = Delivery::findOrFail($deliveryId);
            if ($delivery->passcode !== $data['passcode']) {
                return back()->withErrors(['passcode' => 'Incorrect passcode for one or more selections.']);
            }
            $delivery->driver_pickup_date = Carbon::today();
            $delivery->driver_pickup_time = Carbon::now()->format('H:i:s');
            $delivery->save();
        }

        return redirect()->back()->with('success', 'Deliveries confirmed successfully.');
    }
}

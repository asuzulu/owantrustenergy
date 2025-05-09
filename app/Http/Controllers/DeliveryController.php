<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Delivery;
use App\Models\Cylinder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

            // ── ALPHANUMERIC PASSCODE GENERATION ───────────────────────────────────────────
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
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

    /**
     * Show driver profile (and assigned deliveries for manager/agent/employee or driver view).
     */
    public function driverProfile($id)
    {
        // Load user (driver)
        $user = User::findOrFail($id);

        // Fetch *all* deliveries assigned to this driver, ordered most recent first
        $drvDeliveries = Delivery::where('driver_id', $id)
            ->orderByDesc('delivery_date')
            ->get();

        return view('drivers.profile', compact('user', 'drvDeliveries'));
    }

    public function startDelivery($id)
    {
        $delivery = Delivery::findOrFail($id);

        $delivery->delivery_start = Carbon::now();
        $delivery->save();

        return redirect()->back()->with('success', 'Delivery started.');
    }

    /**
     * Handle the photo upload, stamp date_delivered/time_delivered,
     * store the image, and redirect back.
     */
    public function storeDeliveryImage(Request $request, $cylinder)
    {
        $delivery = Delivery::where('cylinder', ltrim($cylinder, '0'))->firstOrFail();

        // Validate incoming file
        $request->validate([
            'delivery_image' => 'required|image|max:5120', // up to 5MB
        ]);

        // Store the uploaded image under public/delivery_images/
        $path = $request->file('delivery_image')
            ->store('delivery_images', 'public');

        // Update delivery record: image path + stamp delivered date/time
        $delivery->image_path      = $path;
        $delivery->date_delivered  = Carbon::today();
        $delivery->time_delivered  = Carbon::now()->format('H:i:s');
        $delivery->save();

        return redirect()
            ->route('drivers.delivering', $delivery->cylinder)
            ->with([
                'success'   => 'Delivery completed successfully.',
                'imagePath' => $path,
            ]);
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

    public function destroy(Request $request)
    {
        $delivery = Delivery::find($request->id);
        if ($delivery) {
            $delivery->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    /**
     * Manager/Employee clicked "Approve Delivery"
     */
    public function approve(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);
        $approver = Auth::user();

        // Log full delivery info in user-friendly format to storage/logs/deliveries.log
        Log::channel('deliveries')->info(sprintf(
            "Approved Delivery | Cylinder: %s | Driver: %s (%d) | Customer: %s (%d) | Address: %s | Assigned: %s | Pickup: %s %s | Delivered: %s %s | Image: %s | Approved by: %s (%d)",
            $delivery->cylinder,
            $delivery->driver,
            $delivery->driver_id,
            $delivery->customer,
            $delivery->customer_id,
            $delivery->address,
            $delivery->date_assigned,
            $delivery->driver_pickup_date,
            $delivery->driver_pickup_time,
            $delivery->date_delivered,
            $delivery->time_delivered,
            $delivery->image_path,
            $approver->name,
            $approver->id
        ));

        // Mark approved (optional, for audit)
        $delivery->approval = 'approved';
        $delivery->save();

        // Remove record from deliveries table now it's logged ──
        $delivery->delete();

        return back()->with('success', 'Delivery approved and archived.');
    }

    /**
     * Manager/Employee clicked "Disapprove Delivery"
     */
    public function disapprove(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);

        // Mark disapproved
        $delivery->approval = 'disapproved';
        $delivery->save();

        // (Leave the record in the table so they can re-review if needed)

        return back()->with('success', 'Delivery disapproved.');
    }
}

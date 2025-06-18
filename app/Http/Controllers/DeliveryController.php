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

        return view('management.deliveryapproval', compact('deliveries', 'search'));
    }

    /**
     * Mark this delivery as approved.
     */
    public function approve(Delivery $delivery)
    {
        $delivery->approval = 'approved';
        $delivery->save();

        return redirect()
            ->back()
            ->with('success', 'Delivery has been approved.');
    }

    /**
     * Mark this delivery as disapproved.
     */
    public function disapprove(Request $request, Delivery $delivery)
    {
        // 1) Validate that a non-empty "reason" field was submitted:
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        // 2) Count words in the submitted reason (split on whitespace, ignore empty segments):
        $text      = trim($request->input('reason'));
        $wordCount = count(array_filter(preg_split('/\s+/', $text), fn($w) => strlen($w) > 0));

        if ($wordCount < 10) {
            // If fewer than 10 words, redirect back with an error—but
            // do NOT reveal "10 words" requirement, just a generic message:
            return redirect()
                ->back()
                ->withErrors(['reason' => 'Response is too short.'])
                ->withInput();
        }

        // 3) Save "disapproved" status and store the reason on the model:
        $delivery->approval = 'disapproved';
        $delivery->disapproval_reason  = $text;
        $delivery->save();

        // 4) Redirect back with success message
        return redirect()
            ->back()
            ->with('success', 'Delivery has been disapproved.');
    }

    /**
     * Show the “Review Delivery” page.
     */
    public function review(Delivery $delivery)
    {
        // Load relationships if necessary (e.g., customer, driver) and return the review view
        $delivery->load('customerUser');

        // pass the delivery (with image_path, approval, etc.) into a dedicated view
        return view('deliveries.review', [
            'delivery' => $delivery,
            'driverResponse' => $delivery->disapproval_driver_response,
            'customerResponse' => $delivery->disapproval_customer_response,
            'imagePath' => $delivery->image_path,
            'approvalStatus' => $delivery->approval,
        ]);
    }

    public function updateApproval(Request $request)
    {
        // 1) First validate and grab everything
        $data = $request->validate([
            'deliveries'   => 'required|array',
            'deliveries.*' => 'exists:deliveries,id',
            'action'       => 'required|in:approved,disapproved',
            'reason'       => 'nullable|string|min:10', // we'll enforce word-count below
        ]);

        $action = $data['action'];
        $reason = $data['reason'] ?? '';

        // 2) If disapproving, ensure at least 10 words
        if ($action === 'disapproved') {
            $words = preg_split('/\s+/', trim($reason));
            $count = count(array_filter($words, fn($w) => strlen($w) > 0));
            if ($count < 10) {
                return back()
                    ->withErrors(['reason' => 'Response is too short.'])
                    ->withInput();
            }
        }

        // 3) Apply in a transaction
        \DB::transaction(function () use ($data, $action, $reason) {
            foreach ($data['deliveries'] as $id) {
                $update = ['approval' => $action];

                if ($action === 'disapproved') {
                    $update['disapproval_reason'] = $reason;
                }

                Delivery::where('id', $id)->update($update);
            }
        });

        // 4) Redirect with success
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

        // Check each delivery's passcode first
        foreach ($data['selected_deliveries'] as $deliveryId) {
            $delivery = Delivery::findOrFail($deliveryId);
            if ($delivery->passcode !== $data['passcode']) {
                // If AJAX: return JSON error
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Incorrect passcode for one or more selections.',
                    ], 422);
                }
                // Otherwise redirect back with error
                return back()->withErrors(['passcode' => 'Incorrect passcode for one or more selections.']);
            }
        }

        // All passcodes matched: update each delivery
        foreach ($data['selected_deliveries'] as $deliveryId) {
            $delivery = Delivery::findOrFail($deliveryId);
            $delivery->driver_pickup_date = Carbon::today()->toDateString();
            $delivery->driver_pickup_time = Carbon::now()->format('H:i:s');
            $delivery->save();
        }

        // If AJAX: return JSON success
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Deliveries confirmed successfully.',
            ]);
        }

        // Fallback: regular redirect
        return redirect()->back()->with('success', 'Deliveries confirmed successfully.');
    }

    public function start($id)
    {
        $delivery = Delivery::findOrFail($id);

        $user = Auth::user();

        // Only allow the assigned driver or a manager/employee to start the delivery
        if (
            $user->id !== $delivery->driver_id &&
            !in_array($user->position, ['Manager', 'Employee'])
        ) {
            abort(403, 'Unauthorized action.');
        }

        $delivery->delivery_start = Carbon::now();
        $delivery->save();

        // Use a distinct session key so we don’t trigger the “return to warehouse” success modal
        return redirect()->back()->with('delivery_started', 'Delivery has been started.');
    }

    /**
     * AJAX: Return details of the customer for the Contact‑Customer modal.
     */
    public function customerInfo(Request $request)
    {
        $request->validate(['delivery_id' => 'required|exists:deliveries,id']);
        $delivery = Delivery::findOrFail($request->delivery_id);
        $user     = $delivery->customerUser;

        return response()->json([
            'name'  => "{$user->first_name} {$user->last_name}",
            'email' => $user->email,
            'phone' => $user->phone,
        ]);
    }

    /**
     * AJAX: Return details of the driver for the Contact‑Driver modal.
     */
    public function driverInfo(Request $request)
    {
        $request->validate(['delivery_id' => 'required|exists:deliveries,id']);
        $delivery = Delivery::findOrFail($request->delivery_id);
        $user     = $delivery->driverUser;

        return response()->json([
            'name'  => "{$user->first_name} {$user->last_name}",
            'email' => $user->email,
            'phone' => $user->phone,
        ]);
    }
}

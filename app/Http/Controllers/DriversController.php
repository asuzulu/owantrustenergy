<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\State;
use App\Models\Delivery;
use App\Models\Cylinder;

class DriversController extends Controller
{
    public function index()
    {
        if (Auth::user()->position === 'Customer') {
            abort(403, 'Unauthorized action.');
        }
        try {
            $drivers = User::where('position', 'Driver')->paginate(10);
            $states  = State::all();
        } catch (\Exception $e) {
            Log::debug('Error retrieving Drivers: ', ['error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve drivers.');
        }
        return view('management.drivers', compact('drivers', 'states'));
    }

    public function show($id)
    {
        $user               = User::findOrFail($id);
        $warehouseCylinders = collect();
        return view('drivers.profile', compact('user', 'warehouseCylinders'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName'   => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'lastName'    => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'phoneNumber' => 'required|digits:10',
            'gender'      => 'required|string|in:male,female',
            'street'      => 'required|string|max:255',
            'city'        => 'required|string|max:255',
            'state'       => 'required|exists:states,id',
            'bvn'         => 'required|digits:11',
            'nin'         => 'required|digits:11',
            'email'       => ['required', 'email', 'max:255', 'email:rfc,dns', 'unique:users,email'],
            'dob'         => ['required', 'date', 'before:' . now()->subYears(18)->toDateString()],
            'password'    => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/'
            ],
        ], [
            'dob.before'       => 'You must be at least 18 years old to register.',
            'password.regex'   => 'Password must include at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.',
            'firstName.regex'  => 'First name can only contain letters and spaces.',
            'lastName.regex'   => 'Last name can only contain letters and spaces.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $v = $validator->validated();

        try {
            $driver = User::create([
                'first_name'   => $v['firstName'],
                'last_name'    => $v['lastName'],
                'phone_number' => $v['phoneNumber'],
                'gender'       => $v['gender'],
                'street'       => $v['street'],
                'city'         => $v['city'],
                'state'        => State::where('id', $v['state'])->value('name'),
                'bvn'          => $v['bvn'],
                'nin'          => $v['nin'],
                'email'        => $v['email'],
                'dob'          => $v['dob'],
                'password'     => Hash::make($v['password']),
                'position'     => 'Driver',
            ]);

            return response()->json(['success' => true, 'message' => 'Driver registered successfully!']);
        } catch (\Exception $e) {
            Log::error('Driver registration error: ' . $e->getMessage());
            $msg = config('app.debug') ? $e->getMessage() : 'Failed to register driver.';
            return response()->json(['success' => false, 'message' => $msg], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'firstName'   => 'required|string|max:255',
            'lastName'    => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:20',
            'gender'      => 'required|string|max:10',
            'street'      => 'required|string|max:255',
            'city'        => 'required|string|max:255',
            'state'       => 'required|exists:states,id',
            'email'       => 'required|email|max:255',
            'dob'         => 'required|date',
        ]);

        $state     = State::find($request->state);
        $stateName = $state ? $state->name : null;
        $driver    = User::findOrFail($id);

        $driver->update([
            'first_name'   => $request->firstName,
            'last_name'    => $request->lastName,
            'phone_number' => $request->phoneNumber,
            'gender'       => $request->gender,
            'street'       => $request->street,
            'city'         => $request->city,
            'state'        => $stateName,
            'email'        => $request->email,
            'dob'          => $request->dob,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'user' => $driver, 'message' => 'Profile updated successfully!']);
        }

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    public function dashboard()
    {
        $user = Auth::user();
        if ($user->position !== 'Driver') {
            return redirect()->route('dashboard')->withErrors(['Unauthorized access.']);
        }

        $deliveries = Delivery::where('driver', $user->first_name . ' ' . $user->last_name)
            ->whereNull('date_delivered')
            ->orderBy('delivery_date', 'desc')
            ->paginate(10);

        return view('drivers.cylinders', compact('deliveries', 'user'));
    }

    public function driverProfile($id)
    {
        $user               = User::findOrFail($id);
        $deliveries         = Delivery::where('driver_id', $id)->paginate(10);
        $cylinders          = Cylinder::whereIn('id', $deliveries->pluck('cylinder'))->get()->keyBy('id');
        $totalCylinders     = $deliveries->count();
        $warehouseCylinders = collect();
        $states             = State::all();

        return view('drivers.profile', compact(
            'user',
            'deliveries',
            'cylinders',
            'totalCylinders',
            'warehouseCylinders',
            'states'
        ));
    }

    public function showCylinder($id)
    {
        $cylinder   = Cylinder::findOrFail($id);
        $warehouses = DB::table('warehouses')->get();
        return view('cylinders.show', compact('cylinder', 'warehouses'));
    }

    public function delivering($cylinderId)
    {
        $paddedId = str_pad($cylinderId, 9, '0', STR_PAD_LEFT);
        return view('drivers.delivering', compact('paddedId'));
    }

    /**
     * Process the delivery image upload, mark delivered, and log.
     */
    public function storeDeliveryImage(Request $request, $cylinderId)
    {
        $request->validate([
            'delivery_image' => 'required|image|max:5120',
        ]);

        $driver   = Auth::user();
        $paddedId = str_pad($cylinderId, 9, '0', STR_PAD_LEFT);
        $date     = now()->format('Y-m-d');
        $ext      = $request->file('delivery_image')->extension();

        // Retrieve existing delivery so we know the customer
        $delivery = DB::table('deliveries')
            ->where('cylinder', (int)$cylinderId)
            ->first();

        $filename = "{$driver->first_name} {$driver->last_name}_{$paddedId}-{$date}-{$delivery->customer}.{$ext}";

        // Store the image under storage/app/public/deliveries/
        $path = $request->file('delivery_image')
            ->storeAs('public/deliveries', $filename);

        // Update the deliveries table with the image path
        DB::table('deliveries')
            ->where('cylinder', (int)$cylinderId)
            ->update([
                'date_delivered' => now()->toDateString(),
                'time_delivered' => now()->format('H:i:s'),
                'image_path'     => $path,
            ]);

        // Fetch updated record for logging
        $updated = DB::table('deliveries')
            ->where('cylinder', (int)$cylinderId)
            ->first();

        $logMessage = sprintf(
            "Delivery for Cylinder #%s\n" .
                "Driver: %s %s\n" .
                "Set Delivery Date: %s\n" .
                "Set Delivery Time: %s\n" .
                "Delivered: %s\n" .
                "Customer: %s\n" .
                "Size: %s\n" .
                "Image Path: %s\n\n",
            $paddedId,
            $driver->first_name,
            $driver->last_name,
            $updated->delivery_date,
            $updated->delivery_time,
            now()->toDateTimeString(),
            $updated->customer,
            $updated->size,
            $path
        );

        Log::channel('deliveries')->info($logMessage);

        return redirect()
            ->route('drivers.delivering', $paddedId)
            ->with('success', 'Delivery recorded successfully.')
            ->with('imagePath', $path);
    }
}

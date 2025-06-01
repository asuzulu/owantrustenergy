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
        $validatedData = $request->validate([
            'firstName' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'lastName' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'phoneNumber' => 'required|digits:10',
            'gender' => 'required|string|in:male,female',
            'street' => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]+/'],
            'city' => 'required|string|max:255',
            'state' => 'required|exists:states,id',
            'bvn' => 'required|digits:11',
            'nin' => 'required|digits:11',
            'email' => ['required', 'email', 'max:255', 'email', 'unique:users,email'],
            'dob' => [
                'required',
                'date',
                'before:' . now()->subYears(18)->toDateString(), // Ensuring the user is at least 16 years old
                'after:' . now()->subYears(130)->toDateString(), // Ensuring the user is not older than 130 years
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',       // At least one uppercase
                'regex:/[a-z]/',       // At least one lowercase
                'regex:/[0-9]/',       // At least one number
                'regex:/[@$!%*?&]/'    // At least one special character
            ],
            'position' => 'nullable|string|max:255',
            'photo_id' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'dob.before' => 'You must be at least 16 years old to register.',
            'dob.after' => 'The date of birth must not be older than 130 years.',
            'password.regex' => 'Password must include at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.',
            'phoneNumber.regex' => 'Phone number must be a valid number (e.g., 08012345678).',
            'firstName.regex' => 'First name can only contain letters and spaces.',
            'lastName.regex' => 'Last name can only contain letters and spaces.',
        ]);

        $position = $validatedData['position'] ?? 'Customer';

        try {
            $user = User::create([
                'first_name'   => $validatedData['firstName'],
                'last_name'    => $validatedData['lastName'],
                'phone_number' => $validatedData['phoneNumber'],
                'gender'       => $validatedData['gender'],
                'street'       => $validatedData['street'],
                'city'         => $validatedData['city'],
                'state'        => State::where('id', $validatedData['state'])->value('name'),
                'bvn'          => $validatedData['bvn'],
                'nin'          => $validatedData['nin'],
                'email'        => $validatedData['email'],
                'dob'          => $validatedData['dob'],
                'password'     => Hash::make($validatedData['password']),
                'position'     => $position,
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
            'street' => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]+/'],
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
        return view('drivers.delivering', compact('paddedId', 'cylinderId'));
    }

    /**
     * Process the delivery image upload, mark delivered, and log.
     */
    public function storeDeliveryImage(Request $request, $paddedId)
    {
        $request->validate([
            'delivery_image' => 'required|image|max:15000',
        ]);

        $driver   = Auth::user();
        $paddedId = str_pad($paddedId, 9, '0', STR_PAD_LEFT);
        $date     = now()->format('Y-m-d');
        $ext      = $request->file('delivery_image')->extension();

        $rawCylinder = (int) ltrim($paddedId, '0');
        $delivery = DB::table('deliveries')
            ->where('cylinder', $paddedId)
            ->first();

        // Sanitize filename
        $rawName = "{$driver->first_name} {$driver->last_name}";
        $rawCustomer = $delivery->customer;
        $filename = str_replace(' ', '_', "{$rawName}_{$paddedId}-{$date}-{$rawCustomer}.{$ext}");

        // Store the image
        $path = $request->file('delivery_image')
            ->storeAs('deliveries', $filename, 'public');

        // Update database
        DB::table('deliveries')
            ->where('cylinder', $paddedId)
            ->update([
                'date_delivered' => now()->toDateString(),
                'time_delivered' => now()->format('H:i:s'),
                'image_path'     => $path,
            ]);

        // Fetch updated record
        $updated = DB::table('deliveries')
            ->where('cylinder', $paddedId)
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

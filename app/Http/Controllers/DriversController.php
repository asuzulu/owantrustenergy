<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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
            // Ensure $drivers is paginated so the view can call ->hasPages()
            $drivers = User::where('position', 'Driver')->paginate(10);
            $states = State::all();
        } catch (\Exception $e) {
            Log::debug('Error retrieving Drivers: ', ['error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve drivers.');
        }
        // Note: returning the view from management folder
        return view('management.drivers', compact('drivers', 'states'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
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
                'regex:/[A-Z]/', // At least one uppercase
                'regex:/[a-z]/', // At least one lowercase
                'regex:/[0-9]/', // At least one number
                'regex:/[@$!%*?&]/' // At least one special character
            ],
        ], [
            'dob.before'      => 'You must be at least 18 years old to register.',
            'password.regex'  => 'Password must include at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.',
            'phoneNumber.regex' => 'Phone number must be a valid number (e.g., 08012345678).',
            'firstName.regex' => 'First name can only contain letters and spaces.',
            'lastName.regex'  => 'Last name can only contain letters and spaces.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        try {
            $driver = User::create([
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
                'position'     => 'Driver',
            ]);

            return response()->json(['success' => true, 'message' => 'Driver registered successfully!']);
        } catch (\Exception $e) {
            \Log::error('Driver registration error: ' . $e->getMessage());
            if (config('app.debug')) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return response()->json(['success' => false, 'message' => 'Failed to register driver.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Validate request
        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:20',
            'gender' => 'required|string|max:10',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|exists:states,id', // Ensure valid state ID
            'email' => 'required|email|max:255',
            'dob' => 'required|date',
        ]);

        // Retrieve the state name
        $state = State::find($request->state);
        $stateName = $state ? $state->name : null; // Ensure state name is not null

        // Find the driver user
        $driver = User::findOrFail($id);

        // Update user details
        $driver->update([
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'phone_number' => $request->phoneNumber,
            'gender' => $request->gender,
            'street' => $request->street,
            'city' => $request->city,
            'state' => $stateName, // Use fetched state name
            'email' => $request->email,
            'dob' => $request->dob,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'user' => $driver,
                'message' => 'Profile updated successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
    
    public function dashboard()
    {
        $user = Auth::user();

        // Ensure the user is a driver
        if ($user->position !== 'Driver') {
            return redirect()->route('dashboard')->withErrors(['Unauthorized access.']);
        }

        // Fetch deliveries assigned to the logged-in driver
        $deliveries = Delivery::where('driver', $user->first_name . ' ' . $user->last_name)
            ->orderBy('delivery_date', 'desc')
            ->paginate(10);

        return view('drivers.cylinders', compact('deliveries', 'user'));
    }

    public function driverProfile($id)
    {
        $user = User::findOrFail($id);

        // Fetch deliveries assigned to the driver
        $deliveries = Delivery::where('driver_id', $id)->paginate(10);

        // Fetch related cylinders
        $cylinders = Cylinder::whereIn('id', $deliveries->pluck('cylinder'))->get()->keyBy('id');

        // Count total assigned cylinders
        $totalCylinders = $deliveries->count();

        // Initialize $warehouseCylinders as an empty collection
        $warehouseCylinders = collect();

        // Retrieve all states for the edit dropdown
        $states = State::all();

        // Return the driver profile view with all variables defined
        return view('drivers.profile', compact('user', 'deliveries', 'cylinders', 'totalCylinders', 'warehouseCylinders', 'states'));
    }

    public function showCylinder($id)
    {
        $cylinder = Cylinder::findOrFail($id);
        $warehouses = DB::table('warehouses')->get();
        return view('cylinders.show', compact('cylinder', 'warehouses'));
    }
}

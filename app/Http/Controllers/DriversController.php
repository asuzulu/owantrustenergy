<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
        $validatedData = $request->validate([
            'firstName'   => 'required|string|max:255',
            'lastName'    => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:15',
            'gender'      => 'required|string',
            'street'      => 'required|string|max:255',
            'city'        => 'required|string|max:255',
            'state'       => 'required|exists:states,id',
            'bvn'         => 'required|digits:11',
            'nin'         => 'required|digits:11',
            'email'       => 'required|email|unique:users,email',
            'dob'         => 'required|date|before:today',
            'password'    => 'required|string|min:8|confirmed',
            'position'    => 'required|string|in:Driver',
        ]);

        try {
            $stateName = State::where('id', $validatedData['state'])->value('name');

            User::create([
                'first_name'   => $validatedData['firstName'],
                'last_name'    => $validatedData['lastName'],
                'phone_number' => $validatedData['phoneNumber'],
                'gender'       => $validatedData['gender'],
                'street'       => $validatedData['street'],
                'city'         => $validatedData['city'],
                'state'        => $stateName,
                'bvn'          => $validatedData['bvn'],
                'nin'          => $validatedData['nin'],
                'email'        => $validatedData['email'],
                'dob'          => $validatedData['dob'],
                'password'     => Hash::make($validatedData['password']),
                'position'     => $validatedData['position'],
            ]);

            return redirect()->route('drivers.index')->with('success', 'Driver added successfully.');
        } catch (\Exception $e) {
            Log::debug('Error adding Driver: ', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to add driver.');
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

    public function destroy($id)
    {
        $driver = User::findOrFail($id);

        if (Auth::user()->position !== 'Manager') {
            return redirect()->route('drivers.index')->with('error', 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $driver->delete();
            DB::commit();
            return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete driver.');
        }
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
}

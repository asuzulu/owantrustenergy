<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Cylinder;
use Illuminate\Support\Facades\Log;
use App\Models\Warehouse;

class UserController extends Controller
{
    // Show the registration form
    public function showRegisterForm()
    {
        $states = State::all(); // Fetch all states from the database
        return view('register', compact('states')); // Pass states to the view
    }

    // Handle user registration
    public function store(Request $request)
    {
        // Validate user input
        $validatedData = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:15',
            'gender' => 'required|string',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|exists:states,id',
            'bvn' => 'required|digits:11',
            'nin' => 'required|digits:11',
            'email' => 'required|email|unique:users,email',
            'dob' => 'required|date|before:today',
            'password' => 'required|string|min:8|confirmed',
            'position' => 'nullable|string|max:255',
        ]);

        // Default position is 'Customer' if not provided
        $position = $validatedData['position'] ?? 'customer';

        try {
            // Create the new user in the database
            $user = User::create([
                'first_name' => $validatedData['firstName'],
                'last_name' => $validatedData['lastName'],
                'phone_number' => $validatedData['phoneNumber'],
                'gender' => $validatedData['gender'],
                'street' => $validatedData['street'],
                'city' => $validatedData['city'],
                'state' => $validatedData['state'],
                'bvn' => $validatedData['bvn'],
                'nin' => $validatedData['nin'],
                'email' => $validatedData['email'],
                'dob' => $validatedData['dob'],
                'password' => Hash::make($validatedData['password']),
                'position' => $position,
            ]);

            Log::info('New user registered: ' . $user->email); // Log user registration

            // Automatically log in the user after registration
            Auth::login($user);

            // Redirect the user to their respective dashboard
            return $this->redirectUserByPosition($user);
        } catch (\Exception $e) {
            Log::error('User registration failed: ' . $e->getMessage()); // Log error
            return back()->withErrors('Registration failed. Please try again.');
        }
    }

    // Redirect the user based on their position
    private function redirectUserByPosition($user)
    {
        if ($user->position === 'customer') {
            return redirect()->route('dashboard.customer');
        } elseif ($user->position === 'employee') {
            return redirect()->route('dashboard.employee');
        } elseif ($user->position === 'Manager') {
            return redirect()->route('dashboard.management');
        }
        return redirect()->route('dashboard.home'); // Default fallback
    }

    // Show dashboard with cylinders
    public function showDashboard()
    {
        Log::debug('Entering showDashboard method');  // Log entry into method

        // Check if the user is authenticated
        if (Auth::check()) {
            Log::debug('User is authenticated');
        } else {
            Log::debug('User is not authenticated');
        }

        // Get the logged-in user's ID
        $userId = Auth::id();
        Log::debug('Logged-in User ID: ' . $userId);  // Log user ID

        // Fetch cylinders assigned to the logged-in user
        $cylinders = Cylinder::where('user_id', $userId)->get();
        Log::debug('Fetched Cylinders: ', $cylinders->toArray());  // Log fetched cylinders

        // Count the number of cylinders assigned to the user
        $totalCylinders = $cylinders->count();
        Log::debug('Total Cylinders Count: ' . $totalCylinders);  // Log total cylinders count

        // Pass the total cylinders to the view
        return view('dashboard.home', compact('cylinders', 'totalCylinders'));
    }

    // Update the profile image
    public function updateProfileImage(Request $request, $id)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::findOrFail($id);

        // Delete old image if it exists
        if ($user->profile_image) {
            Storage::disk('public')->delete('profile-images/' . $user->profile_image);
        }

        // Create new filename format: firstName_lastName_YYYYMMDD.extension
        $filename = $user->first_name . '_' . $user->last_name . '_' . now()->format('Ymd') . '.' . $request->file('profile_image')->getClientOriginalExtension();

        // Store image
        $imagePath = $request->file('profile_image')->storeAs('profile-images', $filename, 'public');

        // Update user record
        $user->update([
            'profile_image' => $filename,
        ]);

        return redirect()->route('profile.view', $user->id)->with('success', 'Profile image updated successfully.');
    }

    // Show the profile for a specific user
    public function profile($id)
    {
        $user = User::findOrFail($id);

        // Fetch cylinders linked to the user
        $cylinders = Cylinder::where('user_id', $id)->get();

        // Calculate the user's age
        $user->age = \Carbon\Carbon::parse($user->dob)->age;

        // Fetch warehouse cylinders for managers and employees
        $warehouseCylinders = collect();

        if (in_array(Auth::user()->position, ['Manager', 'Employee'])) {
            // Fetch cylinders where the location matches warehouse names or are unassigned
            $warehouseCylinders = Cylinder::whereIn('location', Warehouse::pluck('name')->toArray())
                ->orWhereNull('user_id') // Include unassigned cylinders
                ->select(['id', 'size'])
                ->get();
        }

        // Fetch other users if the logged-in user is a manager
        $users = null;
        if (Auth::user()->position === 'Manager') {
            $users = User::all();
        }

        return view('users.profile', compact('user', 'users', 'cylinders', 'warehouseCylinders'));
    }

    // Show the edit form for a user
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $states = State::all(); // Fetch all states from the database
        return view('users.edit', compact('user', 'states'));
    }

    // Update the user's profile
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $updated = $user->update([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'         => $request->email,
            'phone_number'  => $request->phone_number,
            'city'          => $request->city,
            'state' => State::where('id', $request->state)->value('name'), // Get the state name
        ]);

        return redirect()->route('users.profile', $id)->with('success', 'User details updated successfully.');
    }
}

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
    public function updateProfileImage(Request $request)
    {
        $validated = $request->validate([
            'profile_image' => 'required|file|mimes:jpg,jpeg,png|max:102400', // max file size in KB (100MB)
        ]);

        $user = Auth::user(); // Get the currently authenticated user

        // Handle file upload and update logic
        $filename = strtolower($user->first_name . '-' . $user->last_name . '-' . time() . '.' . $request->file('profile_image')->extension());

        $file = $request->file('profile_image');
        $path = 'user_images/' . $filename;

        // Store the file
        Storage::disk('public')->put($path, file_get_contents($file));

        // Update user's profile image
        $user->profile_image = $filename;
        $user->save();

        return redirect()->route('dashboard.profile')->with('success', 'Profile image updated successfully!');
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
            $warehouseCylinders = Cylinder::where('location', 'like', '%warehouse%')
                ->whereNull('user_id') // Only unassigned cylinders
                ->select(['id', 'size']) // Fetch only needed columns
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
        return view('users.edit', compact('user'));  // Ensure it's users.edit, not users.profile-edit
    }

    // Update the user's profile
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone_number' => 'required|string|max:15',
            'position' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->all());

        return redirect()->route('users.profile', $id)->with('success', 'User details updated successfully.');
    }
}

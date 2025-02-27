<!--?php

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
            'photo_id' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation for the NIN image
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

            // Log the user registration
            Log::info("New user registered: {$user->first_name} {$user->last_name} ({$user->email})");

            // Redirect user to the dashboard or home page after registration
            return redirect()->route('dashboard.cylinder', ['userId' => Auth::id()])->with('success', 'Registration successful');
        } catch (\Exception $e) {
            Log::error("Registration failed: " . $e->getMessage());
            return back()->with('error', 'Registration failed, please try again later.');
        }
    }

    // Upload NIN
    public function uploadNIN(Request $request, $id)
    {
        $request->validate([
            'nin_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = User::findOrFail($id);

        // Generate filename based on user ID (keep it the same)
        $filename = $user->photo_id ?? ($user->id . '.' . $request->nin_image->extension());

        // Define storage path
        $storagePath = 'public/nin-images/' . $filename;

        // Store new file (overwrite if exists)
        $request->nin_image->storeAs('nin-images', $filename, 'public');

        // Ensure the filename is stored in the database if it was previously null
        if (!$user->photo_id) {
            $user->photo_id = $filename;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'photo_id' => $user->photo_id,
            'preview_url' => asset('storage/nin-images/' . $user->photo_id),
        ]);
    }

    // Redirect the user based on their position
    private function redirectUserByPosition($user)
    {
        if ($user->position === 'Customer') {
            return redirect()->route('dashboard.profile');
        } elseif ($user->position === 'Employee') {
            return redirect()->route('dashboard.employee');
        } elseif ($user->position === 'Manager') {
            return redirect()->route('dashboard.management');
        } elseif ($user->position === 'Agent') {
            return redirect()->route('dashboard.agent');
        } elseif ($user->position === 'Driver') {
            return redirect()->route('dashboard.driver');
        }
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
        return view('dashboard.profile', compact('cylinders', 'totalCylinders'));
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

        // Filter out only the fields that are present in the request
        $updateData = array_filter([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'         => $request->email,
            'phone_number'  => $request->phone_number,
            'street'        => $request->street,
            'city'          => $request->city,
            'state'         => $request->state ? State::where('id', $request->state)->value('name') : null,
        ], function ($value) {
            return $value !== null && $value !== '';
        });

        // Update only if there are fields to update
        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return redirect()->route('users.profile', $id)->with('success', 'User details updated successfully.');
    }

    //Assign Cylinder to Customer from Cylinder page
    public function assignCylinder(Request $request, Cylinder $cylinder)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->position !== 'Customer') {
            return response()->json(['error' => 'Only customers can be assigned cylinders.'], 400);
        }

        $cylinder->update([
            'user_id' => $user->id,
            'location' => $user->first_name . ' ' . $user->last_name
        ]);

        return response()->json(['success' => 'Cylinder assigned successfully.']);
    }
}

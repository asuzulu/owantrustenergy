<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Hash;
use App\Models\Cylinder;
use App\Models\Warehouse;

class UserController extends Controller
{
    public function showRegisterForm()
    {
        $states = State::all();
        return view('register', compact('states'));
    }

    public function registerModal(Request $request)
    {
        $validatedData = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:15',
            'email' => 'required|email|unique:users,email',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|exists:states,id',
            'bvn' => 'required|digits:11',
            'nin' => 'required|digits:11',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create the user
        try {
            $user = User::create([
                'first_name' => $validatedData['firstName'],
                'last_name' => $validatedData['lastName'],
                'phone_number' => $validatedData['phoneNumber'],
                'email' => $validatedData['email'],
                'dob' => $validatedData['dob'],
                'gender' => $validatedData['gender'],
                'street' => $validatedData['street'],
                'city' => $validatedData['city'],
                'state' => State::find($validatedData['state'])->name,
                'bvn' => $validatedData['bvn'],
                'nin' => $validatedData['nin'],
                'password' => Hash::make($validatedData['password']),
                'position' => 'Customer',
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'User registered successfully!']);
            } else {
                return redirect()->route('managemnt.accounts')->with('success', 'Registration successful.');
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to register user.'], 500);
            } else {
                return back()->with('error', 'Registration failed, please try again later.');
            }
        }
    }


    public function store(Request $request)
    {
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
            'photo_id' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Set default position to "Customer" (capital C)
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

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'User registered successfully!']);
            } else {
                return redirect()->route('dashboard.profile')->with('success', 'Registration successful.');
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to register user.'], 500);
            } else {
                return back()->with('error', 'Registration failed, please try again later.');
            }
        }
    }


    // Handle NIN image upload
    public function uploadNin(Request $request, $id)
    {
        $request->validate([
            'nin_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = User::findOrFail($id);
        $imageName = $user->id . '.jpg';

        $path = $request->file('nin_image')->storeAs('nin-images', $imageName);

        $user->photo_id = $imageName;
        $user->save();

        return response()->json([
            'success' => true,
            'photo_id' => $imageName,
            'preview_url' => asset('storage/nin-images/' . $imageName),
        ]);
    }

    private function redirectUserByPosition($user)
    {
        return match ($user->position) {
            'Customer' => redirect()->route('dashboard.profile'),
            'Employee' => redirect()->route('dashboard.employee'),
            'Manager' => redirect()->route('management.home'),
            'Agent' => redirect()->route('dashboard.agent'),
            'Driver' => redirect()->route('dashboard.driver'),
            default => redirect('/'),
        };
    }

    public function showDashboard()
    {
        $userId = Auth::id();
        $cylinders = Cylinder::where('user_id', $userId)->get();
        $totalCylinders = $cylinders->count();

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

    public function profile($id)
    {
        $user = User::findOrFail($id);
        $cylinders = Cylinder::where('user_id', $id)->get();
        $user->age = \Carbon\Carbon::parse($user->dob)->age;
        $warehouseCylinders = collect();

        if (in_array(Auth::user()->position, ['Manager', 'Employee'])) {
            $warehouseCylinders = Cylinder::whereIn('location', Warehouse::pluck('name')->toArray())
                ->orWhereNull('user_id')
                ->select(['id', 'size'])
                ->get();
        }

        $users = Auth::user()->position === 'Manager' ? User::all() : null;

        return view('users.profile', compact('user', 'users', 'cylinders', 'warehouseCylinders'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $states = State::all();
        return view('users.edit', compact('user', 'states'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $updateData = array_filter([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'street' => $request->street,
            'city' => $request->city,
            'state' => $request->state ? State::where('id', $request->state)->value('name') : null,
        ], fn($value) => !is_null($value) && $value !== '');

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return redirect()->route('users.profile', $id)->with('success', 'User details updated successfully.');
    }
}

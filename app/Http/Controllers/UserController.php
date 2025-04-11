<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Hash;
use App\Models\Cylinder;
use App\Models\Warehouse;
use Carbon\Carbon;

class UserController extends Controller
{
    public function showRegisterForm()
    {
        $states = State::all();
        return view('register', compact('states'));
    }

    public function registerModal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'lastName' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'phoneNumber' => 'required|digits:10',
            'gender' => 'required|string|in:male,female',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|exists:states,id',
            'bvn' => 'required|digits:11',
            'nin' => 'required|digits:11',
            'email' => ['required', 'email', 'max:255', 'email:rfc,dns', 'unique:users,email'],
            'dob' => ['required', 'date', 'before:' . now()->subYears(18)->toDateString()],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',       // At least one uppercase
                'regex:/[a-z]/',       // At least one lowercase
                'regex:/[0-9]/',       // At least one number
                'regex:/[@$!%*?&]/'     // At least one special character
            ],
            'position' => 'nullable|string|max:255',
            'photo_id' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'dob.before' => 'You must be at least 18 years old to register.',
            'password.regex' => 'Password must include at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.',
            'phoneNumber.regex' => 'Phone number must be a valid number (e.g., 08012345678).',
            'firstName.regex' => 'First name can only contain letters and spaces.',
            'lastName.regex' => 'Last name can only contain letters and spaces.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
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

            return response()->json(['success' => true, 'message' => 'User registered successfully!']);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            if (config('app.debug')) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return response()->json(['success' => false, 'message' => 'Failed to register user.'], 500);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'firstName' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'lastName' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'phoneNumber' => 'required|digits:10',
            'gender' => 'required|string|in:male,female',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|exists:states,id',
            'bvn' => 'required|digits:11',
            'nin' => 'required|digits:11',
            'email' => ['required', 'email', 'max:255', 'email:rfc,dns', 'unique:users,email'],
            'dob' => ['required', 'date', 'before:' . now()->subYears(18)->toDateString()],
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
            'dob.before' => 'You must be at least 18 years old to register.',
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

            return redirect()->route('dashboard.profile')->with('success', 'Registration successful.');
        } catch (\Exception $e) {
            return back()->with('error', 'Registration failed, please try again later.');
        }
    }

    // Handle NIN image upload
    public function uploadNin(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->hasFile('nin_image')) {
            $file = $request->file('nin_image');

            // Remove old image if exists
            if ($user->photo_id && Storage::disk('public')->exists('nin-images/' . $user->photo_id)) {
                Storage::disk('public')->delete('nin-images/' . $user->photo_id);
            }

            // Generate new filename
            $filename = strtolower($user->last_name . '-' . $user->first_name . '-' . $user->id . '.' . $file->getClientOriginalExtension());

            // Store image
            $file->storeAs('nin-images', $filename, 'public');

            // Update database
            $user->photo_id = $filename;
            $user->save();

            return response()->json([
                'success' => true,
                'preview_url' => asset('storage/nin-images/' . $filename),
            ]);
        }

        return response()->json(['success' => false], 400);
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
        $user->age = Carbon::parse($user->dob)->age;
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

        // Return the response as JSON with a success message and redirect URL
        return response()->json([
            'success' => true,
            'message' => 'User details updated successfully.',
            'redirect' => route('users.profile', $id),
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Detach related data (set user_id to null for related cylinders)
        Cylinder::where('user_id', $user->id)->update(['user_id' => null]);

        // Delete the user
        $user->delete();

        // Return JSON response with redirect URL
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
            'redirect' => route('management.cylinders')  // Redirect to management cylinders page
        ]);
    }
}
